create table app
(
    id     int unsigned auto_increment
        primary key,
    name   varchar(255) default '' not null comment '应用名称',
    `key`  varchar(255)            not null comment '应用KEY 别名',
    remark varchar(255) default '' not null comment '备注',
    status tinyint      default 0  not null comment '状态',
    sort   int          default 0  not null comment '排序 ASC'
)
    comment '应用列表' row_format = DYNAMIC;

create table config
(
    id      int unsigned auto_increment
        primary key,
    `key`   varchar(50) default '' not null comment '标签',
    title   varchar(30) default '' not null comment '名称',
    `group` varchar(30) default '' not null comment '分组',
    type    varchar(30) default '' not null comment '数据类型',
    value   longtext               null comment '变量值'
)
    comment '配置';

create table file_menu
(
    id   int unsigned auto_increment
        primary key,
    name varchar(255) not null comment '名称'
)
    comment '文件分组';

create table file_storage
(
    id         int unsigned auto_increment
        primary key,
    name       varchar(255)                 not null comment '名称',
    `key`      varchar(20)                  not null comment '唯一标识',
    config     json                         null comment '配置',
    is_default tinyint          default 0   not null comment '是否默认',
    status     tinyint unsigned default '0' not null comment '状态 1=正常 0=停用',
    constraint `key`
        unique (`key`)
)
    comment '文件存储' row_format = DYNAMIC;

create table file
(
    id          int unsigned auto_increment
        primary key,
    file_name   varchar(255)             not null comment '文件名',
    file_type   varchar(50)              not null comment '文件类型（MIME类型，如 image/png）',
    file_ext    varchar(20)              not null comment '文件后缀（如 .jpg/.pdf）文件后缀',
    file_size   bigint                   not null comment '文件大小（字节）',
    url         varchar(255)             not null comment '链接地址',
    file_path   varchar(1024)            not null comment '存储路径',
    menu_id     int unsigned default '0' not null,
    storage_id  int unsigned             not null comment '存储方式id',
    storage_key varchar(20)              not null comment '储存方式key',
    hash_md5    char(32)     default ''  not null comment '文件内容的MD5',
    user_id     int unsigned default '0' not null comment '上传用户id',
    status      tinyint      default 1   not null comment '状态 1=正常 0=停用',
    created_at  datetime                 not null comment '创建时间',
    updated_at  datetime                 not null comment '更新时间',
    deleted_at  datetime                 null comment '删除时间',
    constraint file_file_storage_id_fk
        foreign key (storage_id) references file_storage (id)
)
    comment '文件' row_format = DYNAMIC;

create table log_type
(
    id     int unsigned auto_increment comment '<10为系统日志'
        primary key,
    app_id int unsigned default '0' not null comment '应用ID 0为通用',
    name   char(32)     default ''  not null comment '中文名称',
    label  char(32)     default ''  not null comment '英文别名',
    remark varchar(255) default ''  not null comment '备注',
    status tinyint      default 1   not null comment '日志开启状态',
    color  varchar(255) default ''  not null comment '日志颜色 #ff0000'
)
    comment '日志级别' row_format = DYNAMIC;

create table log
(
    id              int unsigned auto_increment
        primary key,
    app_id          int unsigned     default '0'    not null comment '应用ID 0为未知',
    type_id         int unsigned     default '0'    not null comment '日志级别 <10为系统日志',
    type_name       varchar(32)      default ''     not null comment '日志级别名称',
    title           varchar(500)     default ''     not null comment '标题',
    value           text                            null comment '日志内容',
    value_type      varchar(32)      default 'text' not null comment '日志类型  text,json,html',
    request_source  varchar(255)     default ''     not null comment '请求来源',
    request_ip      varchar(64)      default ''     not null comment '请求来源IP',
    request_user_id int unsigned     default '0'    not null comment '操作人ID',
    request_user    varchar(255)     default ''     not null comment '操作人',
    status          tinyint unsigned default '0'    not null comment '状态 0=未处理 1=已查看 2=已处理',
    created_at      datetime                        not null comment '创建时间',
    updated_at      datetime                        not null comment '更新时间',
    constraint log_log_type_id_fk
        foreign key (type_id) references log_type (id)
)
    comment '日志表' row_format = DYNAMIC;

create index log_log_level_id_fk
    on log (type_id);

create table menu
(
    id        int unsigned auto_increment
        primary key,
    app_id    int unsigned default '0' not null comment '应用ID',
    pid       int unsigned default '0' not null comment '上级ID',
    name      varchar(255) default ''  not null comment '别名',
    title     varchar(255) default ''  not null comment '显示名称',
    type      char(12)     default ''  not null comment '类型',
    path      varchar(255) default ''  not null comment '路由地址',
    component varchar(255) default ''  not null comment '组件地址',
    sort      int unsigned default '0' not null comment '排序',
    meta      json                     null comment 'meta路由参数',
    constraint menu_app_id_fk
        foreign key (app_id) references app (id)
)
    comment '菜单' row_format = DYNAMIC;

create index sort
    on menu (sort);

create table menu_api
(
    id      int unsigned auto_increment
        primary key,
    app_id  int unsigned default '0' not null comment '应用ID',
    menu_id int unsigned             not null comment '菜单ID',
    path    varchar(255) default ''  not null comment 'API路由地址',
    tag     varchar(255) default ''  not null comment '标识',
    constraint menu_api_app_id_fk
        foreign key (app_id) references app (id),
    constraint menu_api_menu_id_fk
        foreign key (menu_id) references menu (id)
)
    comment '菜单权限接口' row_format = DYNAMIC;

create index menu_id
    on menu_api (menu_id);

create index path
    on menu_api (path);

create table role
(
    id            int unsigned auto_increment
        primary key,
    app_id        int unsigned     default '0' not null comment '应用ID',
    name          char(12)         default ''  not null comment '角色名称',
    rules         varchar(1000)    default ''  not null comment '权限ID ,分割',
    rules_checked varchar(1000)    default ''  not null comment '权限树选中的字节点ID',
    remark        varchar(255)     default ''  not null comment '简介',
    status        tinyint unsigned default '0' not null comment '状态',
    sort          int unsigned     default '0' not null comment '排序',
    is_admin      tinyint unsigned default '0' not null comment '是否为管理员（所有权限）',
    created_at    datetime                     not null comment '创建时间',
    updated_at    datetime                     not null comment '更新时间',
    deleted_at    datetime                     null comment '删除时间',
    constraint role_ibfk_1
        foreign key (app_id) references app (id)
)
    comment '用户角色' row_format = DYNAMIC;

create index role_app_id_fk
    on role (app_id);

create index sort
    on role (sort);

create table task
(
    id         int unsigned auto_increment
        primary key,
    title      varchar(255)            default ''       not null comment '任务标题',
    `group`    enum ('user', 'system') default 'system' not null comment '分组',
    user_id    int unsigned            default '0'      not null comment '关联用户ID',
    type       varchar(255)                             not null comment '任务类型',
    label      varchar(255)            default ''       not null comment '任务标识，用于区分业务',
    content    longtext                                 null comment '任务内容',
    result     longtext                                 null comment '任务结果',
    status     tinyint                 default 0        not null comment '状态 0=待处理 1=处理中 2=已完成 -1=失败',
    created_at datetime                                 not null comment '创建时间',
    updated_at datetime                                 not null comment '更新时间',
    deleted_at datetime                                 null comment '删除时间'
)
    comment '任务';

create table user
(
    id         int unsigned auto_increment
        primary key,
    nickname   char(32)     default '' not null comment '昵称',
    username   char(32)     default '' not null comment '用户名(登录账号)',
    mobile     char(15)                not null comment '手机号',
    avatar     varchar(255) default '' not null comment '头像',
    password   char(32)     default '' not null comment '密码 md5',
    status     tinyint      default 0  not null comment '状态',
    login_time datetime                null comment '登录时间',
    created_at datetime                not null comment '创建时间',
    updated_at datetime                not null comment '更新时间',
    deleted_at datetime                null comment '删除时间',
    constraint username_unique
        unique (username, deleted_at)
)
    comment '用户表' row_format = DYNAMIC;

create table user_role
(
    id         int unsigned auto_increment
        primary key,
    user_id    int unsigned not null comment '用户ID',
    role_id    int unsigned not null comment '角色ID',
    created_at datetime     not null comment '创建时间',
    deleted_at datetime     null comment '删除时间',
    constraint user_role_role_id_fk
        foreign key (role_id) references role (id),
    constraint user_role_user_id_fk
        foreign key (user_id) references user (id)
)
    comment '用户角色' row_format = DYNAMIC;
