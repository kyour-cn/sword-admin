<template>
  <el-container class="page-user">
    <el-aside style="width: 240px;">
      <el-container>
        <el-header style="height: auto;display: block;">
          <div class="user-info-top">
            <el-avatar :size="70" src="/admin/img/avatar.png"></el-avatar>
            <h2>{{ user.userName }}</h2>
            <p>
              <el-tag class="user-info-role" v-for="role in user.role" effect="dark" round size="large" disable-transitions>{{ role }}</el-tag>
            </p>
          </div>
        </el-header>
        <el-main class="nopadding">
          <el-menu class="menu" :default-active="page">
            <el-menu-item-group v-for="group in menu" :key="group.groupName" :title="group.groupName">
              <el-menu-item v-for="item in group.list" :key="item.component" :index="item.component" @click="openPage">
                <el-icon v-if="item.icon"><component :is="item.icon"/></el-icon>
                <template #title>
                  <span>{{item.title}}</span>
                </template>
              </el-menu-item>
            </el-menu-item-group>
          </el-menu>
        </el-main>
      </el-container>
    </el-aside>
    <el-main>
      <Suspense>
        <template #default>
          <component :is="page"/>
        </template>
        <template #fallback>
          <el-skeleton :rows="3" />
        </template>
      </Suspense>
    </el-main>
  </el-container>
</template>

<script>
  import { defineAsyncComponent } from 'vue'
  import tool from "@/utils/tool.js";

  export default {
    name: 'user_center',
    components: {
      account: defineAsyncComponent(() => import('./user/account')),
      seting: defineAsyncComponent(() => import('./user/seting')),
      pushSettings: defineAsyncComponent(() => import('./user/pushSettings')),
      password: defineAsyncComponent(() => import('./user/password')),
      // space: defineAsyncComponent(() => import('./user/space')),
      logs: defineAsyncComponent(() => import('./user/logs')),
      // upToEnterprise: defineAsyncComponent(() => import('./user/upToEnterprise'))
    },
    data() {
      return {
        menu: [
          {
            groupName: "基本设置",
            list: [
              {
                icon: "el-icon-postcard",
                title: "账号信息",
                component: "account"
              },
              {
                icon: "el-icon-operation",
                title: "*个人设置",
                component: "seting"
              },
              {
                icon: "el-icon-lock",
                title: "密码",
                component: "password"
              },
              {
                icon: "el-icon-bell",
                title: "*通知设置",
                component: "pushSettings"
              }
            ]
          },
          {
            groupName: "数据管理",
            list: [
              // {
              //   icon: "el-icon-coin",
              //   title: "存储空间信息",
              //   component: "space"
              // },
              {
                icon: "el-icon-clock",
                title: "*操作日志",
                component: "logs"
              }
            ]
          },
          // {
          //   groupName: "账号升级",
          //   list: [
          //     {
          //       icon: "el-icon-office-building",
          //       title: "升级为企业账号",
          //       component: "upToEnterprise"
          //     }
          //   ]
          // }
        ],
        user: {
          userName: "",
          role: [],
        },
        page: "account"
      }
    },
    mounted() {
      const userInfo = tool.data.get("USER_INFO")
      if(userInfo) {
        this.user.userName = userInfo.nickname || ""
        if(userInfo.user_role) {
          userInfo.user_role.forEach(item => {
            this.user.role.push(item.role.name)
          })
        }
      }
      console.log(userInfo)
    },
    //路由跳转进来 判断from是否有特殊标识做特殊处理
    beforeRouteEnter (to, from, next){
      next((vm)=>{
        if(from.is){
          //删除特殊标识，防止标签刷新重复执行
          delete from.is
          //执行特殊方法
          vm.$alert('路由跳转过来后含有特殊标识，做特殊处理', '提示', {
            type: 'success',
            center: true
          }).then(() => {}).catch(() => {})
        }
      })
    },
    methods: {
      openPage(item){
        this.page = item.index
      }
    }
  }
</script>

<style lang="scss" scoped>
.page-user {
  .user-info-top {
    text-align: center;
  }

  .user-info-top h2 {
    font-size: 18px;
    margin-top: 5px;
  }

  .user-info-top p {
    margin: 8px 0 10px 0;
  }

  .menu {
    background: none;
  }

  .menu .el-menu-item {
    font-size: 12px;
    --el-menu-item-height: 50px;
  }

  .menu .el-menu-item-group {
    border-top: 1px solid var(--el-border-color-light);
  }

  .menu .el-menu-item-group:first-child {
    border: 0;
  }

  .user-info-role {
    margin: 2px;
  }
}
</style>
