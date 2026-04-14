<template>
  <div :dir="direction">
    <div v-if="theme === 'frontend'">
      <FrontendNavbarComponent />
      <FrontendCartComponent />
      <router-view></router-view>
      <FrontendMobileNavBarComponent />
      <FrontendMobileAccountComponent />
      <FrontendCookiesComponent />
      <FrontendFooterComponent />
    </div>

    <div v-if="theme === 'backend'">
      <main class="db-main" :class="parseInt(authInfo.role) === 5 ? 'expand' : ''" v-if="logged">
        <BackendNavbarComponent />
        <BackendMenuComponent v-if="parseInt(authInfo.role) != 5" />
        <router-view></router-view>
      </main>

      <div v-if="!logged">
        <router-view></router-view>
      </div>
    </div>

    <div v-if="theme === 'table'">
      <TableNavbarComponent />
      <TableCartComponent />
      <router-view></router-view>
      <TableFooterComponent />
    </div>
  </div>
</template>

<script>
import BackendNavbarComponent from "./layouts/backend/BackendNavbarComponent";
import BackendMenuComponent from "./layouts/backend/BackendMenuComponent";
import FrontendNavbarComponent from "./layouts/frontend/FrontendNavBarComponent";
import FrontendFooterComponent from "./layouts/frontend/FrontendFooterComponent";
import FrontendMobileNavBarComponent from "./layouts/frontend/FrontendMobileNavBarComponent";
import FrontendMobileAccountComponent from "./layouts/frontend/FrontendMobileAccountComponent";
import FrontendCartComponent from "./layouts/frontend/FrontendCartComponent";
import FrontendCookiesComponent from "./layouts/frontend/FrontendCookiesComponent";
import TableNavbarComponent from "./layouts/table/TableNavBarComponent.vue";
import TableFooterComponent from "./layouts/table/TableFooterComponent.vue";
import TableCartComponent from "./layouts/table/TableCartComponent.vue";
import displayModeEnum from "../enums/modules/displayModeEnum";
import env from "../config/env";
import roleEnum from "../enums/modules/roleEnum";

export default {
  name: "DefaultComponent",
  components: {
    TableCartComponent,
    TableFooterComponent,
    TableNavbarComponent,
    FrontendCartComponent,
    FrontendMobileAccountComponent,
    FrontendMobileNavBarComponent,
    FrontendCookiesComponent,
    FrontendFooterComponent,
    FrontendNavbarComponent,
    BackendNavbarComponent,
    BackendMenuComponent,
  },
  data() {
    return {
      theme: "frontend",
      enums: {
        roleEnum: roleEnum
      }
    };
  },
  computed: {
    direction: function () {
      return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
    },
    logged: function () {
      return this.$store.getters.authStatus;
    },
    authInfo: function () {
      return this.$store.getters.authInfo;
    },
  },
  beforeMount() {
    this.$store
      .dispatch("frontendSetting/lists")
      .then((res) => {
        this.$store.dispatch("globalState/init", {
          branch_id: res.data.data.site_default_branch,
          language_id: res.data.data.site_default_language,
        });
      })
      .catch();


    if(env.DEMO ==="true" ||  env.DEMO ===true || env.DEMO ==="1" || env.DEMO ===1)
    {
      this.$store.dispatch("authcheck").then(res => {
      if (res.data.status === false && (this.theme == "frontend" || this.theme == "backend")) {
        this.$router.push({ name: "frontend.home" });
      };
    }).catch();
    }

  },
  watch: {
    $route(e) {
      if (e.meta.isFrontend === true) {
        this.theme = "frontend";
      } else if (e.meta.isTable === true) {
        this.theme = "table";
      } else {
        this.theme = "backend";
      }
    },
  },
};
</script>
