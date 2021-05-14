SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for rbac_admin
-- ----------------------------
DROP TABLE IF EXISTS `rbac_admin`;
CREATE TABLE `rbac_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `username` varchar(255) NOT NULL DEFAULT '' COMMENT '账号',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态[0=禁用1=正常]',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `update_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  `access_token` varchar(255) DEFAULT '' COMMENT '授权码',
  `access_token_expire_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '授权码过期时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='权限-管理员表';

-- ----------------------------
-- Records of rbac_admin
-- ----------------------------
BEGIN;
INSERT INTO `rbac_admin` VALUES (1, 'super_admin', '$2y$10$Sq36wGYTYx3tPWv6Ecp0yuJl1/PcGZvT8kLy9Ntkv8SRKsgEiRrKG', 1, '2021-04-09 14:14:00', '2021-05-14 22:22:49', '13f6ecc4cbd43939b5b5b55783d29d13', '2021-05-21 22:22:49');
COMMIT;

-- ----------------------------
-- Table structure for rbac_admin_menu
-- ----------------------------
DROP TABLE IF EXISTS `rbac_admin_menu`;
CREATE TABLE `rbac_admin_menu` (
  `admin_id` int(11) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `menu_id` int(11) NOT NULL DEFAULT 0 COMMENT '菜单id',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '菜单层级路径',
  `level` int(11) NOT NULL COMMENT '菜单等级',
  `parent_id` int(11) DEFAULT 0 COMMENT '父级id',
  `sort` int(11) DEFAULT 0 COMMENT '排序'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员-菜单分配表';

-- ----------------------------
-- Records of rbac_admin_menu
-- ----------------------------
BEGIN;
INSERT INTO `rbac_admin_menu` VALUES (1, 1, '0', 0, 0, 1);
INSERT INTO `rbac_admin_menu` VALUES (1, 2, '0,1', 1, 1, 1);
INSERT INTO `rbac_admin_menu` VALUES (1, 3, '0,1', 1, 1, 2);
INSERT INTO `rbac_admin_menu` VALUES (1, 4, '0,1', 1, 1, 4);
INSERT INTO `rbac_admin_menu` VALUES (1, 5, '0,1', 1, 1, 5);
INSERT INTO `rbac_admin_menu` VALUES (1, 6, '0,1', 1, 1, 6);
INSERT INTO `rbac_admin_menu` VALUES (1, 7, '0,1', 1, 1, 7);
INSERT INTO `rbac_admin_menu` VALUES (1, 8, '0,1', 1, 1, 3);
COMMIT;

-- ----------------------------
-- Table structure for rbac_menu
-- ----------------------------
DROP TABLE IF EXISTS `rbac_menu`;
CREATE TABLE `rbac_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `parent_id` int(11) NOT NULL DEFAULT 0 COMMENT '父级id',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `update_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  `sort` int(11) DEFAULT 0 COMMENT '排序',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '菜单层级路径',
  `level` int(11) NOT NULL DEFAULT 0 COMMENT '菜单层级',
  `href` varchar(255) DEFAULT '' COMMENT '菜单地址',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COMMENT='权限-菜单表';

-- ----------------------------
-- Records of rbac_menu
-- ----------------------------
BEGIN;
INSERT INTO `rbac_menu` VALUES (1, '权限管理', 0, '2021-05-02 02:55:59', '2021-05-09 12:48:46', 1, '0', 0, '');
INSERT INTO `rbac_menu` VALUES (2, '管理员管理', 1, '2021-05-02 02:56:11', '2021-05-07 18:33:21', 1, '0,1', 1, './module/permission/admin.html');
INSERT INTO `rbac_menu` VALUES (3, '接口权限管理', 1, '2021-05-02 02:56:28', '2021-05-09 12:48:15', 2, '0,1', 1, './module/permission/permission.html');
INSERT INTO `rbac_menu` VALUES (4, '角色管理', 1, '2021-05-02 02:57:20', '2021-05-02 02:57:20', 4, '0,1', 1, './module/permission/role.html');
INSERT INTO `rbac_menu` VALUES (5, '接口权限分配管理', 1, '2021-05-02 02:57:38', '2021-05-02 02:57:38', 5, '0,1', 1, './module/permission/rolePermissionAssign.html');
INSERT INTO `rbac_menu` VALUES (6, '角色分配管理', 1, '2021-05-02 02:57:51', '2021-05-02 02:57:51', 6, '0,1', 1, './module/permission/adminRoleAssign.html');
INSERT INTO `rbac_menu` VALUES (7, '菜单分配管理', 1, '2021-05-02 02:58:00', '2021-05-02 02:58:00', 7, '0,1', 1, './module/permission/adminMenuAssign.html');
INSERT INTO `rbac_menu` VALUES (8, '菜单管理', 1, '2021-05-02 02:56:28', '2021-05-09 01:39:03', 3, '0,1', 1, './module/permission/menu.html');
COMMIT;

-- ----------------------------
-- Table structure for rbac_permission
-- ----------------------------
DROP TABLE IF EXISTS `rbac_permission`;
CREATE TABLE `rbac_permission` (
  `api_app` varchar(255) NOT NULL DEFAULT '' COMMENT '接口端入口',
  `api_version` varchar(255) NOT NULL DEFAULT '' COMMENT '接口版本',
  `api_class` varchar(255) NOT NULL COMMENT '接口类',
  `api_function` varchar(255) NOT NULL COMMENT '接口函数',
  `api_route` varchar(255) NOT NULL COMMENT '接口路由',
  `api_name` varchar(255) DEFAULT '' COMMENT '接口名称',
  PRIMARY KEY (`api_route`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='所有权限[用于保存遍历出来的所有权限]';

-- ----------------------------
-- Records of rbac_permission
-- ----------------------------
BEGIN;
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Admin', 'createAdmin', '/Admin/v1/Admin/createAdmin', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Admin', 'deleteAdminById', '/Admin/v1/Admin/deleteAdminById', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Admin', 'getAdminByAccessToken', '/Admin/v1/Admin/getAdminByAccessToken', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Admin', 'getAdminDetailById', '/Admin/v1/Admin/getAdminDetailById', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Admin', 'getAdminList', '/Admin/v1/Admin/getAdminList', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Admin', 'getMenu', '/Admin/v1/Admin/getMenu', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Admin', 'isAccessTokenEffective', '/Admin/v1/Admin/isAccessTokenEffective', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Admin', 'login', '/Admin/v1/Admin/login', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Admin', 'logout', '/Admin/v1/Admin/logout', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Admin', 'updateAdminById', '/Admin/v1/Admin/updateAdminById', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Admin', 'updateAdminPasswordByAccessToken', '/Admin/v1/Admin/updateAdminPasswordByAccessToken', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Admin', 'updateAdminStatusById', '/Admin/v1/Admin/updateAdminStatusById', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'AdminMenu', 'assignAdminMenu', '/Admin/v1/AdminMenu/assignAdminMenu', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'AdminMenu', 'getAdminMenuByAdminId', '/Admin/v1/AdminMenu/getAdminMenuByAdminId', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Menu', 'createMenu', '/Admin/v1/Menu/createMenu', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Menu', 'deleteMenuById', '/Admin/v1/Menu/deleteMenuById', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Menu', 'getChildMenuList', '/Admin/v1/Menu/getChildMenuList', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Menu', 'getMenuDetailById', '/Admin/v1/Menu/getMenuDetailById', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Menu', 'getMenuList', '/Admin/v1/Menu/getMenuList', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Menu', 'searchParentMenu', '/Admin/v1/Menu/searchParentMenu', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Menu', 'updateMenuById', '/Admin/v1/Menu/updateMenuById', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Permission', 'generateFilePermissions', '/Admin/v1/Permission/generateFilePermissions', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Permission', 'getPermissionList', '/Admin/v1/Permission/getPermissionList', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Role', 'createRole', '/Admin/v1/Role/createRole', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Role', 'deleteRoleById', '/Admin/v1/Role/deleteRoleById', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Role', 'getRoleList', '/Admin/v1/Role/getRoleList', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Role', 'updateRoleById', '/Admin/v1/Role/updateRoleById', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'Role', 'updateRoleStatusById', '/Admin/v1/Role/updateRoleStatusById', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'RoleAdmin', 'assignAdminRole', '/Admin/v1/RoleAdmin/assignAdminRole', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'RoleAdmin', 'deleteAdminRoleByAdminId', '/Admin/v1/RoleAdmin/deleteAdminRoleByAdminId', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'RoleAdmin', 'getAdminRoleByAdminId', '/Admin/v1/RoleAdmin/getAdminRoleByAdminId', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'RolePermission', 'assignRolePermission', '/Admin/v1/RolePermission/assignRolePermission', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'RolePermission', 'deleteRolePermissionByRoutes', '/Admin/v1/RolePermission/deleteRolePermissionByRoutes', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v1', 'RolePermission', 'getRolePermissionByRoleId', '/Admin/v1/RolePermission/getRolePermissionByRoleId', '');
INSERT INTO `rbac_permission` VALUES ('Admin', 'v2', 'Test', 'testFunction', '/Admin/v2/Test/testFunction', '');
COMMIT;

-- ----------------------------
-- Table structure for rbac_role
-- ----------------------------
DROP TABLE IF EXISTS `rbac_role`;
CREATE TABLE `rbac_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '角色名称',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `update_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  `parent_id` int(11) DEFAULT 0 COMMENT '父级id',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态[0=禁用1=正常2=删除]',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='权限-角色表';

-- ----------------------------
-- Records of rbac_role
-- ----------------------------
BEGIN;
INSERT INTO `rbac_role` VALUES (1, '超级管理员', '2021-05-11 01:27:42', '2021-05-13 00:11:24', 0, 1);
COMMIT;

-- ----------------------------
-- Table structure for rbac_role_admin
-- ----------------------------
DROP TABLE IF EXISTS `rbac_role_admin`;
CREATE TABLE `rbac_role_admin` (
  `admin_id` int(11) NOT NULL COMMENT '管理员id',
  `role_id` int(11) NOT NULL COMMENT '角色id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限-管理员角色关联表';

-- ----------------------------
-- Records of rbac_role_admin
-- ----------------------------
BEGIN;
INSERT INTO `rbac_role_admin` VALUES (1, 1);
COMMIT;

-- ----------------------------
-- Table structure for rbac_role_permission
-- ----------------------------
DROP TABLE IF EXISTS `rbac_role_permission`;
CREATE TABLE `rbac_role_permission` (
  `role_id` int(11) NOT NULL COMMENT '角色id',
  `api_app` varchar(255) NOT NULL DEFAULT '' COMMENT '接口端入口',
  `api_version` varchar(255) NOT NULL DEFAULT '' COMMENT '接口版本',
  `api_class` varchar(255) NOT NULL DEFAULT '' COMMENT '接口类',
  `api_function` varchar(255) NOT NULL DEFAULT '' COMMENT '接口函数',
  `api_route` varchar(255) NOT NULL DEFAULT '' COMMENT '接口路由',
  `auth_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '授权类型类型[1=正向授权2=反向授权]'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限-角色权限关联表';

-- ----------------------------
-- Records of rbac_role_permission
-- ----------------------------
BEGIN;
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Admin', 'createAdmin', '/Admin/v1/Admin/createAdmin', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Admin', 'deleteAdminById', '/Admin/v1/Admin/deleteAdminById', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Admin', 'getAdminByAccessToken', '/Admin/v1/Admin/getAdminByAccessToken', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Admin', 'getAdminDetailById', '/Admin/v1/Admin/getAdminDetailById', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Admin', 'getAdminList', '/Admin/v1/Admin/getAdminList', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Admin', 'getMenu', '/Admin/v1/Admin/getMenu', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Admin', 'isAccessTokenEffective', '/Admin/v1/Admin/isAccessTokenEffective', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Admin', 'login', '/Admin/v1/Admin/login', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Admin', 'logout', '/Admin/v1/Admin/logout', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Admin', 'updateAdminById', '/Admin/v1/Admin/updateAdminById', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Admin', 'updateAdminPasswordByAccessToken', '/Admin/v1/Admin/updateAdminPasswordByAccessToken', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Admin', 'updateAdminStatusById', '/Admin/v1/Admin/updateAdminStatusById', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'AdminMenu', 'assignAdminMenu', '/Admin/v1/AdminMenu/assignAdminMenu', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'AdminMenu', 'getAdminMenuByAdminId', '/Admin/v1/AdminMenu/getAdminMenuByAdminId', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Menu', 'createMenu', '/Admin/v1/Menu/createMenu', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Menu', 'deleteMenuById', '/Admin/v1/Menu/deleteMenuById', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Menu', 'getChildMenuList', '/Admin/v1/Menu/getChildMenuList', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Menu', 'getMenuDetailById', '/Admin/v1/Menu/getMenuDetailById', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Menu', 'getMenuList', '/Admin/v1/Menu/getMenuList', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Menu', 'searchParentMenu', '/Admin/v1/Menu/searchParentMenu', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Menu', 'updateMenuById', '/Admin/v1/Menu/updateMenuById', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Permission', 'generateFilePermissions', '/Admin/v1/Permission/generateFilePermissions', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Permission', 'getPermissionList', '/Admin/v1/Permission/getPermissionList', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Role', 'createRole', '/Admin/v1/Role/createRole', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Role', 'deleteRoleById', '/Admin/v1/Role/deleteRoleById', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Role', 'getRoleList', '/Admin/v1/Role/getRoleList', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Role', 'updateRoleById', '/Admin/v1/Role/updateRoleById', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'Role', 'updateRoleStatusById', '/Admin/v1/Role/updateRoleStatusById', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'RoleAdmin', 'assignAdminRole', '/Admin/v1/RoleAdmin/assignAdminRole', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'RoleAdmin', 'deleteAdminRoleByAdminId', '/Admin/v1/RoleAdmin/deleteAdminRoleByAdminId', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'RoleAdmin', 'getAdminRoleByAdminId', '/Admin/v1/RoleAdmin/getAdminRoleByAdminId', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'RolePermission', 'assignRolePermission', '/Admin/v1/RolePermission/assignRolePermission', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'RolePermission', 'deleteRolePermissionByRoutes', '/Admin/v1/RolePermission/deleteRolePermissionByRoutes', 1);
INSERT INTO `rbac_role_permission` VALUES (1, 'Admin', 'v1', 'RolePermission', 'getRolePermissionByRoleId', '/Admin/v1/RolePermission/getRolePermissionByRoleId', 1);
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;