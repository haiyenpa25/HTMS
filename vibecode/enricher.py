"""
vibecode/enricher.py
====================
Phân tích HTMS codebase theo layer kiến trúc (không cần LLM API).
Dùng heuristics dựa trên đường dẫn file và tên class/function.

Output: vibecode_enrichment.json
  {
    "files": {
      "app/Http/Controllers/Admin/UserController.php": {
        "layer": "Controller",
        "domain": "users",
        "keywords": ["user", "permission", "admin"],
        "summary_vi": "Controller quản lý người dùng (Admin)",
        "portal": "admin"
      },
      ...
    },
    "domains": { "auth": [...files], "members": [...files], ... },
    "layers": { "Controller": [...files], "Service": [...files], ... },
    "generated_at": "2026-06-08T..."
  }

Usage:
  python -m vibecode enrich          # từ thư mục gốc CMS
  python vibecode/enricher.py        # chạy trực tiếp
"""

import os
import json
import re
from datetime import datetime
from pathlib import Path

# =====================================================================
# HEURISTIC RULES — Không cần LLM, offline hoàn toàn
# =====================================================================

# Layer detection theo đường dẫn file
LAYER_RULES = [
    # (pattern_in_path, layer_name, color_hex)
    (r'Http/Controllers/Admin',     'AdminController',  '#ef4444'),  # Đỏ
    (r'Http/Controllers/Portal',    'PortalController', '#f97316'),  # Cam
    (r'Http/Controllers/Auth',      'AuthController',   '#eab308'),  # Vàng
    (r'Http/Controllers',           'Controller',       '#f59e0b'),  # Vàng nhạt
    (r'Http/Middleware',            'Middleware',        '#8b5cf6'),  # Tím
    (r'Http/Requests',              'Request',          '#a78bfa'),  # Tím nhạt
    (r'Services/',                  'Service',          '#22c55e'),  # Xanh lá
    (r'Models/',                    'Model',            '#06b6d4'),  # Cyan
    (r'Policies/',                  'Policy',           '#64748b'),  # Xám
    (r'Jobs/',                      'Job',              '#6b7280'),  # Xám đậm
    (r'Events/',                    'Event',            '#84cc16'),  # Xanh vàng
    (r'Listeners/',                 'Listener',         '#4ade80'),  # Xanh nhạt
    (r'resources/js/Pages/Portal',  'VuePage-Portal',   '#3b82f6'),  # Xanh dương
    (r'resources/js/Pages/Ministry','VuePage-Ministry', '#6366f1'),  # Indigo
    (r'resources/js/Pages/Admin',   'VuePage-Admin',    '#ec4899'),  # Hồng
    (r'resources/js/Pages',         'VuePage',          '#60a5fa'),  # Xanh nhạt
    (r'resources/js/Layouts',       'VueLayout',        '#93c5fd'),  # Xanh rất nhạt
    (r'resources/js/Components',    'VueComponent',     '#bfdbfe'),  # Xanh siêu nhạt
    (r'database/migrations',        'Migration',        '#475569'),  # Xám
    (r'database/seeders',           'Seeder',           '#64748b'),  # Xám
    (r'routes/',                    'Routes',           '#f43f5e'),  # Đỏ hồng
    (r'config/',                    'Config',           '#94a3b8'),  # Xám nhạt
    (r'vibecode/',                  'Vibecode-Tool',    '#fbbf24'),  # Vàng sáng
]

# Domain detection theo tên file / đường dẫn
DOMAIN_RULES = [
    (r'(auth|login|logout|password|session)',         'auth',         '🔐'),
    (r'(user|member|profile|account)',                'members',      '👥'),
    (r'(attendance|meeting|session)',                 'attendance',   '📋'),
    (r'(finance|donation|fund|transaction|offering)', 'finance',      '💰'),
    (r'(duty|assignment|roster|schedule)',            'assignments',  '📅'),
    (r'(ministry|education|class)',                   'ministry',     '✝️'),
    (r'(chronicle|diary|notebook|log)',               'chronicles',   '📖'),
    (r'(document|file|upload|asset)',                 'documents',    '📄'),
    (r'(care|visitation|pastoral|visit)',             'care',         '🤝'),
    (r'(permission|feature|role|access|mac)',         'permissions',  '🛡️'),
    (r'(report|statistic|summary|chart)',             'reports',      '📊'),
    (r'(broadcast|notification|alert)',               'broadcast',    '📢'),
    (r'(department|org|team|group)',                  'organization', '🏢'),
    (r'(form|template|survey)',                       'forms',        '📝'),
    (r'(visitor|guest|crm|contact)',                  'crm',          '🌐'),
    (r'(portal|dashboard|layout)',                    'portal',       '🏠'),
    (r'(admin|system|config|setting)',                'admin',        '⚙️'),
]

# Portal detection theo đường dẫn
PORTAL_RULES = [
    (r'/Admin/',            'admin'),
    (r'/Portal/',           'activities'),
    (r'/Ministry/',         'ministry'),
    (r'/Deacon/',           'deacon'),
    (r'/Finance/',          'finance'),
    (r'/Member/',           'member'),
    (r'/Auth/',             'auth'),
    (r'Pages/Ministry',     'ministry'),
    (r'Pages/Portal',       'activities'),
    (r'Pages/Admin',        'admin'),
    (r'Pages/Users',        'admin'),
    (r'routes/',            'global'),
    (r'Models/',            'global'),
    (r'Services/',          'global'),
    (r'Middleware/',        'global'),
]

# Summary templates tiếng Việt theo layer + domain
SUMMARY_TEMPLATES = {
    ('AdminController', 'auth'):         'Controller xử lý xác thực Admin',
    ('AdminController', 'members'):      'Controller quản lý người dùng (Admin)',
    ('AdminController', 'permissions'):  'Controller phân quyền MAC hệ thống',
    ('AdminController', 'finance'):      'Controller quản lý tài chính (Admin)',
    ('AdminController', 'documents'):    'Controller quản lý tài liệu (Admin)',
    ('AdminController', 'care'):         'Controller quản lý chăm sóc mục vụ (Admin)',
    ('PortalController', 'attendance'):  'Controller điểm danh sinh hoạt',
    ('PortalController', 'members'):     'Controller quản lý thành viên portal',
    ('PortalController', 'finance'):     'Controller tài chính phòng ban',
    ('PortalController', 'chronicles'):  'Controller sổ tay hội thánh',
    ('PortalController', 'assignments'): 'Controller phân công / lịch trực',
    ('PortalController', 'care'):        'Controller chăm sóc mục vụ',
    ('Service', 'permissions'):          'Service kiểm tra quyền MAC V2',
    ('Service', 'members'):              'Service xử lý logic thành viên',
    ('Service', 'attendance'):           'Service xử lý logic điểm danh',
    ('Model', 'members'):                'Model dữ liệu thành viên',
    ('Model', 'auth'):                   'Model người dùng & xác thực',
    ('Model', 'finance'):                'Model dữ liệu tài chính',
    ('Model', 'attendance'):             'Model dữ liệu điểm danh',
    ('Model', 'permissions'):            'Model phân quyền MAC V2',
    ('Middleware', 'permissions'):       'Middleware kiểm tra quyền truy cập portal',
    ('Middleware', 'auth'):              'Middleware xác thực người dùng',
    ('VuePage-Portal', 'attendance'):    'Vue page: điểm danh sinh hoạt',
    ('VuePage-Portal', 'members'):       'Vue page: quản lý thành viên portal',
    ('VuePage-Admin', 'members'):        'Vue page: quản lý tài khoản (Admin)',
    ('VuePage-Admin', 'permissions'):    'Vue page: phân quyền MAC (Admin)',
    ('Routes', 'global'):                'Định nghĩa routes toàn hệ thống',
}


class HTMSEnricher:
    """
    Phân tích HTMS codebase theo layer và domain,
    sinh ra vibecode_enrichment.json để dùng cho:
    1. vibecode_graph.html — color-coding, filter, search
    2. docs/KNOWLEDGE_GRAPH.md — index cho AI agents
    3. Skill codebase-navigator — context tiết kiệm token
    """

    SCAN_EXTENSIONS = {'.php', '.vue', '.js', '.ts', '.py'}
    IGNORE_DIRS = {
        'vendor', 'node_modules', '.git', '.vibecode', '__pycache__',
        '.venv', 'storage', 'bootstrap/cache', '.agents', 'public/build'
    }

    def __init__(self, root_dir: str = '.'):
        self.root_dir = Path(root_dir).resolve()
        self.result = {
            "files": {},
            "domains": {},
            "layers": {},
            "portals": {},
            "stats": {},
            "generated_at": datetime.now().isoformat()
        }

    def _detect_layer(self, rel_path: str) -> tuple[str, str]:
        """Trả về (layer_name, color_hex)"""
        for pattern, layer, color in LAYER_RULES:
            if re.search(pattern, rel_path, re.IGNORECASE):
                return layer, color
        # Default theo extension
        ext = Path(rel_path).suffix
        defaults = {
            '.vue': ('VueComponent', '#60a5fa'),
            '.php': ('PHP-Other',   '#7c3aed'),
            '.js':  ('JavaScript',  '#fbbf24'),
            '.ts':  ('TypeScript',  '#3b82f6'),
            '.py':  ('Python',      '#22c55e'),
        }
        return defaults.get(ext, ('Other', '#94a3b8'))

    def _detect_domain(self, rel_path: str) -> tuple[str, str]:
        """Trả về (domain_name, emoji)"""
        filename_lower = rel_path.lower()
        for pattern, domain, emoji in DOMAIN_RULES:
            if re.search(pattern, filename_lower):
                return domain, emoji
        return 'general', '📁'

    def _detect_portal(self, rel_path: str) -> str:
        for pattern, portal in PORTAL_RULES:
            if re.search(pattern, rel_path, re.IGNORECASE):
                return portal
        return 'global'

    def _make_summary(self, layer: str, domain: str, rel_path: str) -> str:
        # 1. Lookup template
        key = (layer, domain)
        if key in SUMMARY_TEMPLATES:
            return SUMMARY_TEMPLATES[key]

        # 2. Generate from filename
        filename = Path(rel_path).stem
        # CamelCase → words
        words = re.sub(r'(?<=[a-z])(?=[A-Z])', ' ', filename)
        words = words.replace('-', ' ').replace('_', ' ')

        layer_vi = {
            'AdminController': 'Controller Admin',
            'PortalController': 'Controller Portal',
            'Controller': 'Controller',
            'Service': 'Service',
            'Model': 'Model',
            'Middleware': 'Middleware',
            'VuePage-Portal': 'Trang Vue (Portal)',
            'VuePage-Ministry': 'Trang Vue (Mục Vụ)',
            'VuePage-Admin': 'Trang Vue (Admin)',
            'VuePage': 'Trang Vue',
            'VueComponent': 'Component Vue',
            'VueLayout': 'Layout Vue',
            'Migration': 'Migration DB',
            'Seeder': 'Seeder dữ liệu',
            'Routes': 'Định nghĩa Routes',
            'Policy': 'Policy phân quyền',
            'Job': 'Background Job',
        }.get(layer, layer)

        return f'{layer_vi}: {words}'

    def _extract_keywords(self, rel_path: str, layer: str, domain: str) -> list[str]:
        """Trích keywords từ tên file + path cho semantic search."""
        keywords = set()

        # Từ đường dẫn
        parts = re.split(r'[/\\._-]', rel_path.lower())
        for p in parts:
            if len(p) > 2 and p not in ('php', 'vue', 'the', 'get', 'set', 'and', 'for'):
                keywords.add(p)

        # Thêm layer và domain
        keywords.add(layer.lower().replace('-', '_'))
        keywords.add(domain)

        # HTMS-specific keywords
        htms_map = {
            'auth': ['login', 'logout', 'password', 'session', 'xac_thuc'],
            'members': ['member', 'thanh_vien', 'user', 'nguoi_dung'],
            'attendance': ['attendance', 'diem_danh', 'meeting', 'sinh_hoat'],
            'finance': ['finance', 'tai_chinh', 'donation', 'dang_hien', 'fund'],
            'assignments': ['assignment', 'phan_cong', 'duty', 'lich_truc'],
            'ministry': ['ministry', 'muc_vu', 'education', 'giao_duc'],
            'chronicles': ['chronicle', 'so_tay', 'diary', 'nhat_ky'],
            'permissions': ['permission', 'phan_quyen', 'mac', 'feature', 'access'],
            'care': ['care', 'cham_soc', 'pastoral', 'visitation', 'tham_vieng'],
        }
        for kw in htms_map.get(domain, []):
            keywords.add(kw)

        return sorted(list(keywords))[:20]  # Giới hạn 20 keywords

    def scan(self) -> dict:
        """Quét toàn bộ codebase và sinh enrichment data."""
        print("[SCAN] Scanning {} ...".format(self.root_dir))
        file_count = 0

        for root, dirs, files in os.walk(self.root_dir):
            # Bỏ qua thư mục ignored
            dirs[:] = [
                d for d in dirs
                if d not in self.IGNORE_DIRS
                and not d.startswith('.')
            ]

            for filename in files:
                full_path = Path(root) / filename
                rel_path = str(full_path.relative_to(self.root_dir)).replace('\\', '/')

                if full_path.suffix not in self.SCAN_EXTENSIONS:
                    continue

                # Bỏ qua file quá lớn (> 500KB)
                try:
                    size = full_path.stat().st_size
                    if size > 500_000:
                        continue
                except Exception:
                    continue

                layer, color = self._detect_layer(rel_path)
                domain, emoji = self._detect_domain(rel_path)
                portal = self._detect_portal(rel_path)
                summary = self._make_summary(layer, domain, rel_path)
                keywords = self._extract_keywords(rel_path, layer, domain)

                self.result["files"][rel_path] = {
                    "layer": layer,
                    "layer_color": color,
                    "domain": domain,
                    "domain_emoji": emoji,
                    "portal": portal,
                    "summary_vi": summary,
                    "keywords": keywords,
                    "size_bytes": size,
                }

                # Index theo domain
                if domain not in self.result["domains"]:
                    self.result["domains"][domain] = []
                self.result["domains"][domain].append(rel_path)

                # Index theo layer
                if layer not in self.result["layers"]:
                    self.result["layers"][layer] = []
                self.result["layers"][layer].append(rel_path)

                # Index theo portal
                if portal not in self.result["portals"]:
                    self.result["portals"][portal] = []
                self.result["portals"][portal].append(rel_path)

                file_count += 1

        # Stats
        self.result["stats"] = {
            "total_files": file_count,
            "total_layers": len(self.result["layers"]),
            "total_domains": len(self.result["domains"]),
            "total_portals": len(self.result["portals"]),
        }

        print("[OK] Analysed {} files | {} layers | {} domains".format(
              file_count, len(self.result['layers']), len(self.result['domains'])))
        return self.result

    def save(self, output_path: str = 'vibecode_enrichment.json') -> str:
        """Lưu kết quả ra JSON."""
        with open(output_path, 'w', encoding='utf-8') as f:
            json.dump(self.result, f, ensure_ascii=False, indent=2)
        size_kb = os.path.getsize(output_path) / 1024
        print("[SAVE] Da luu: {} ({:.1f} KB)".format(output_path, size_kb))
        return output_path

    def generate_knowledge_graph_md(self, output_path: str = 'docs/KNOWLEDGE_GRAPH.md') -> str:
        """
        Sinh docs/KNOWLEDGE_GRAPH.md theo Karpathy-pattern.
        File này dùng như 'bộ nhớ nén' cho AI agents — đọc 1 file thay vì cả codebase.
        """
        lines = [
            "# HTMS Knowledge Graph Index",
            f"> Tạo tự động bởi vibecode/enricher.py — {datetime.now().strftime('%Y-%m-%d %H:%M')}",
            "> Đây là bộ nhớ nén toàn hệ thống. AI agents đọc file này thay vì scan codebase.",
            "",
            "## Tóm Tắt Nhanh",
            "",
            f"- **Tổng files:** {self.result['stats']['total_files']}",
            f"- **Số layers:** {self.result['stats']['total_layers']}",
            f"- **Số domains:** {self.result['stats']['total_domains']}",
            "",
            "---",
            "",
            "## Index Theo Domain (Business Logic)",
            "",
        ]

        domain_order = [
            'permissions', 'auth', 'members', 'attendance', 'assignments',
            'finance', 'ministry', 'chronicles', 'care', 'documents',
            'reports', 'broadcast', 'organization', 'forms', 'crm',
            'portal', 'admin', 'general'
        ]

        for domain in domain_order:
            if domain not in self.result["domains"]:
                continue
            files = self.result["domains"][domain]
            emoji = next(
                (e for _, d, e in DOMAIN_RULES if d == domain),
                '📁'
            )
            lines.append(f"### {emoji} {domain.upper()} ({len(files)} files)")
            lines.append("")
            for f in sorted(files)[:30]:  # Giới hạn 30 per domain
                info = self.result["files"].get(f, {})
                summary = info.get("summary_vi", "")
                layer = info.get("layer", "")
                lines.append(f"- `{f}` [{layer}] — {summary}")
            if len(files) > 30:
                lines.append(f"- *(... và {len(files) - 30} files khác)*")
            lines.append("")

        lines += [
            "---",
            "",
            "## Index Theo Layer (Architecture)",
            "",
        ]

        layer_order = [
            'Middleware', 'Routes',
            'AdminController', 'PortalController', 'AuthController', 'Controller',
            'Service', 'Model', 'Policy',
            'VuePage-Admin', 'VuePage-Portal', 'VuePage-Ministry', 'VuePage', 'VueLayout', 'VueComponent',
            'Migration', 'Seeder',
            'Request', 'Job', 'Event', 'Listener',
            'Config', 'Vibecode-Tool', 'PHP-Other', 'JavaScript', 'TypeScript', 'Python',
        ]

        for layer in layer_order:
            if layer not in self.result["layers"]:
                continue
            files = self.result["layers"][layer]
            lines.append(f"### {layer} ({len(files)} files)")
            lines.append("")
            for f in sorted(files)[:20]:
                lines.append(f"- `{f}`")
            if len(files) > 20:
                lines.append(f"- *(... và {len(files) - 20} files khác)*")
            lines.append("")

        lines += [
            "---",
            "",
            "## Kiến Trúc MAC V2 (Điểm Quan Trọng)",
            "",
            "```",
            "Request → Middleware (CheckPortalAccess / EnsureMinistryContext)",
            "       → PortalService::canAccess(user, dept, feature)",
            "           ├─ Level 1: FeatureAssignmentService (dept config)",
            "           └─ Level 2: UserDepartmentFeature (user override)",
            "```",
            "",
            "**Files cốt lõi MAC V2:**",
            "- `app/Http/Middleware/CheckPortalAccess.php` — Middleware cổng vào portal",
            "- `app/Http/Middleware/PortalAccessMiddleware.php` — Gate kiểm tra feature",
            "- `app/Services/PortalService.php` — Logic canAccess()",
            "- `app/Services/FeatureAssignmentService.php` — Level 1 resolution",
            "- `app/Models/UserDepartmentFeature.php` — Level 2 override table",
            "",
            "---",
            "",
            "## Cách Dùng File Này (Cho AI Agents)",
            "",
            "```",
            "Thay vì: grep cả codebase (500+ files, tốn 10k+ token)",
            "Dùng:    Đọc KNOWLEDGE_GRAPH.md (1 file, ~3k token) → xác định đúng file → đọc file đó",
            "```",
            "",
            "**Ví dụ query:**",
            "- Tìm file xử lý điểm danh → xem section `ATTENDANCE`",
            "- Tìm file kiểm tra quyền → xem section `PERMISSIONS` + 'Files cốt lõi MAC V2'",
            "- Tìm Vue page của portal → xem layer `VuePage-Portal`",
            "",
        ]

        os.makedirs(os.path.dirname(output_path), exist_ok=True)
        with open(output_path, 'w', encoding='utf-8') as f:
            f.write('\n'.join(lines))

        size_kb = os.path.getsize(output_path) / 1024
        print("[DOCS] Da tao Knowledge Graph: {} ({:.1f} KB)".format(output_path, size_kb))
        return output_path


def main():
    """Chay truc tiep: python vibecode/enricher.py"""
    import sys
    root = sys.argv[1] if len(sys.argv) > 1 else '.'
    enricher = HTMSEnricher(root)
    enricher.scan()
    enricher.save('vibecode_enrichment.json')
    enricher.generate_knowledge_graph_md('docs/KNOWLEDGE_GRAPH.md')
    print("")
    print("[DONE] Xong! Chay tiep:")
    print("   python -m vibecode ui   (de mo graph voi day du tinh nang)")


if __name__ == '__main__':
    main()
