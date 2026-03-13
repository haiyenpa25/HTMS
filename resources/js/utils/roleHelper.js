export const roleLabels = {
    'Super_Admin': 'Quản lý Hệ Thống',
    'Pastor': 'Mục sư',
    'BTS_Admin': 'Ban Trị Sự',
    'Department_Lead': 'Trưởng ban',
    'Secretary': 'Thư ký',
    'Team_Lead': 'Trưởng nhóm',
    'Member': 'Tín hữu',
    'Visitation_Staff': 'Nhân viên Thăm viếng',
    'Deacon': 'Chấp sự',
    'Guest': 'Khách',
};

export const getRoleLabel = (roleCode) => {
    if (!roleCode || roleCode === 'Guest') return 'Khách';
    return roleLabels[roleCode] || roleCode;
};
