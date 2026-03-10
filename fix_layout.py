import os, re

pages_dir = r'resources/js/Pages'

# Order matters - more specific patterns first
patterns = [
    (r'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8', 'w-full'),
    (r'max-w-7xl mx-auto px-4 sm:px-6', 'w-full'),
    (r'max-w-\[1400px\] mx-auto', 'w-full'),
    (r'max-w-\[90rem\] mx-auto', 'w-full'),
    (r'max-w-7xl mx-auto', 'w-full'),
]

count = 0
for root, dirs, files in os.walk(pages_dir):
    for fn in files:
        if fn.endswith('.vue'):
            fp = os.path.join(root, fn)
            with open(fp, 'r', encoding='utf-8') as f:
                content = f.read()
            new_content = content
            for pat, repl in patterns:
                new_content = re.sub(pat, repl, new_content)
            if new_content != content:
                with open(fp, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                count += 1
                print(f'  Modified: {fp}')

print(f'\nDone. Modified {count} files.')
