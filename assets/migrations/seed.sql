# table app
INSERT INTO app (id, name, `key`, remark, status, sort) VALUES (1, '系统后台', 'admin', '系统总后台', 1, 0);

# table config
INSERT INTO config (`key`, title, `group`, type, value) VALUES ('site', '站点配置', 'base', 'json', '{"admin_captcha_switch":false}');

# table file_storage
INSERT INTO file_storage (id, name, `key`, config, is_default, status) VALUES (1, '本地储存', 'local', null, 1, 1);

# table log_type
INSERT INTO log_type (id, app_id, name, label, remark, status, color) VALUES (1, 0, '调试', 'debug', '调试信息', 1, '#333333');
INSERT INTO log_type (id, app_id, name, label, remark, status, color) VALUES (2, 0, '信息', 'info', '一般信息', 1, '#3498db');
INSERT INTO log_type (id, app_id, name, label, remark, status, color) VALUES (3, 0, '警告', 'warn', '警告，非错误的异常情况', 1, '#f1c40f');
INSERT INTO log_type (id, app_id, name, label, remark, status, color) VALUES (4, 0, '错误', 'error', '运行时错误，记录并非紧急的问题', 1, '#d63031');
INSERT INTO log_type (id, app_id, name, label, remark, status, color) VALUES (10, 1, '登录日志', 'login', '用户登录时记录的日志', 1, '#23e279');

# table menu
INSERT INTO menu (id, app_id, pid, name, title, type, path, component, sort, meta) VALUES (1, 1, 0, 'home', '首页', 'menu', '/home', '', 0, '{"tag": "", "icon": "el-icon-compass", "type": "menu", "color": "", "title": "首页", "active": "", "hidden": false, "fullpage": false, "hiddenBreadcrumb": false}');
INSERT INTO menu (id, app_id, pid, name, title, type, path, component, sort, meta) VALUES (2, 1, 1, 'dashboard', '控制台', 'menu', '/dashboard', 'admin/home', 0, '{"tag": "", "icon": "el-icon-coin", "type": "menu", "affix": true, "color": "", "title": "控制台", "active": "", "hidden": false, "fullPage": false, "hiddenBreadcrumb": false}');
INSERT INTO menu (id, app_id, pid, name, title, type, path, component, sort, meta) VALUES (3, 1, 0, 'system', '系统管理', 'menu', '/admin', '', 0, '{"tag": "", "icon": "el-icon-cpu", "type": "menu", "color": "", "title": "系统管理", "active": "", "hidden": false, "fullpage": false, "hiddenBreadcrumb": false}');
INSERT INTO menu (id, app_id, pid, name, title, type, path, component, sort, meta) VALUES (4, 1, 3, 'app', '应用管理', 'menu', '/admin/system/app', 'admin/system/app', 1, '{"tag": "", "icon": "el-icon-message-box", "type": "menu", "color": "", "title": "应用管理", "active": "", "hidden": false, "fullPage": false, "hiddenBreadcrumb": false}');
INSERT INTO menu (id, app_id, pid, name, title, type, path, component, sort, meta) VALUES (5, 1, 3, 'menus', '菜单权限', 'menu', '/admin/system/menu', 'admin/system/menu', 2, '{"tag": "", "icon": "el-icon-menu", "type": "menu", "color": "", "title": "菜单权限", "active": "", "hidden": false, "fullpage": false, "hiddenBreadcrumb": false}');
INSERT INTO menu (id, app_id, pid, name, title, type, path, component, sort, meta) VALUES (6, 1, 3, 'role', '角色管理', 'menu', '/admin/system/role', 'admin/system/role', 3, '{"tag": "", "icon": "el-icon-briefcase", "type": "menu", "color": "", "title": "角色管理", "active": "", "hidden": false, "fullpage": false, "hiddenBreadcrumb": false}');
INSERT INTO menu (id, app_id, pid, name, title, type, path, component, sort, meta) VALUES (7, 1, 3, 'user', '用户管理', 'menu', '/admin/system/user', 'admin/system/user', 4, '{"tag": "", "icon": "el-icon-avatar", "type": "menu", "color": "", "title": "用户管理", "active": "", "hidden": false, "fullpage": false, "hiddenBreadcrumb": false}');
INSERT INTO menu (id, app_id, pid, name, title, type, path, component, sort, meta) VALUES (8, 1, 3, 'log', '系统日志', 'menu', '/admin/system/log', 'admin/system/log', 6, '{"tag": "", "icon": "el-icon-document", "type": "menu", "color": "", "title": "系统日志", "active": "", "hidden": false, "fullPage": false, "hiddenBreadcrumb": false}');
INSERT INTO menu (id, app_id, pid, name, title, type, path, component, sort, meta) VALUES (9, 1, 3, 'file', '文件管理', 'menu', '/admin/system/file', 'admin/system/file', 7, '{"tag": "", "icon": "el-icon-folder-opened", "type": "menu", "color": "", "title": "文件管理", "active": "", "hidden": false, "fullPage": false, "hiddenBreadcrumb": false}');
INSERT INTO menu (id, app_id, pid, name, title, type, path, component, sort, meta) VALUES (10, 1, 1, 'user_center', '用户中心', 'menu', '/admin/home/user_center', 'admin/home/user_center', 0, '{"tag": "", "icon": "el-icon-avatar", "type": "menu", "color": "", "title": "用户中心", "active": "", "hidden": true, "fullPage": false, "hiddenBreadcrumb": false}');

# table menu_api
INSERT INTO menu_api (app_id, menu_id, path, tag) VALUES (1, 6, '/admin/system/role/list', '');
INSERT INTO menu_api (app_id, menu_id, path, tag) VALUES (1, 6, '/admin/system/role/add', 'admin.system.role.add');
INSERT INTO menu_api (app_id, menu_id, path, tag) VALUES (1, 6, '/admin/system/role/edit', 'admin.system.role.edit');
INSERT INTO menu_api (app_id, menu_id, path, tag) VALUES (1, 6, '/admin/system/role/delete', 'admin.system.role.delete');
INSERT INTO menu_api (app_id, menu_id, path, tag) VALUES (1, 7, '/admin/system/user/list', '');
INSERT INTO menu_api (app_id, menu_id, path, tag) VALUES (1, 7, '/admin/system/user/add', 'admin.system.user.add');
INSERT INTO menu_api (app_id, menu_id, path, tag) VALUES (1, 7, '/admin/system/user/edit', 'admin.system.user.edit');
INSERT INTO menu_api (app_id, menu_id, path, tag) VALUES (1, 7, '/admin/system/user/delete', 'admin.system.user.delete');
INSERT INTO menu_api (app_id, menu_id, path, tag) VALUES (1, 5, '/admin/system/menu/list', '');
INSERT INTO menu_api (app_id, menu_id, path, tag) VALUES (1, 5, '/admin/system/menu/add', 'admin.system.menu.add');
INSERT INTO menu_api (app_id, menu_id, path, tag) VALUES (1, 5, '/admin/system/menu/edit', 'admin.system.menu.edit');
INSERT INTO menu_api (app_id, menu_id, path, tag) VALUES (1, 5, '/admin/system/menu/delete', 'admin.system.menu.delete');
INSERT INTO menu_api (app_id, menu_id, path, tag) VALUES (1, 4, '/admin/system/app/list', '');
INSERT INTO menu_api (app_id, menu_id, path, tag) VALUES (1, 4, '/admin/system/app/add', 'admin.system.app.add');
INSERT INTO menu_api (app_id, menu_id, path, tag) VALUES (1, 4, '/admin/system/app/edit', 'admin.system.app.edit');
INSERT INTO menu_api (app_id, menu_id, path, tag) VALUES (1, 4, '/admin/system/app/delete', 'admin.system.app.delete');
INSERT INTO menu_api (app_id, menu_id, path, tag) VALUES (1, 8, '/admin/system/log/list', '');
INSERT INTO menu_api (app_id, menu_id, path, tag) VALUES (1, 8, '/admin/system/log/logStat', '');
INSERT INTO menu_api (app_id, menu_id, path, tag) VALUES (1, 8, '/admin/system/log/typeList', '');

# table role
INSERT INTO role (id, app_id, name, rules, rules_checked, remark, status, sort, is_admin, created_at, updated_at, deleted_at) VALUES (1, 1, '管理员', '', '', '', 1, 0, 1, '2025-01-01 00:00:00', '2025-01-01 00:00:00', null);

# table user
INSERT INTO user (id, nickname, username, mobile, avatar, password, status, login_time, created_at, updated_at, deleted_at) VALUES (1, '管理员', 'admin', '', '', '767e955464233667bfd855686a55b352', 1, '2025-01-01 00:00:00', '2025-01-01 00:00:00', '2025-01-01 00:00:00', null);

# table user_role
INSERT INTO user_role (user_id, role_id, created_at, deleted_at) VALUES (1, 1, '2025-01-01 00:00:00', null);
