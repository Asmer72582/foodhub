<template>
  <div class="row">
    <div class="col-12" v-if="parseInt($store.getters.authInfo.role) !== 5">
      <BreadcrumbComponent />
    </div>
    <router-view></router-view>
  </div>
</template>

<script>
import BreadcrumbComponent from "../components/BreadcrumbComponent";
export default {
  name: "ChefComponent",
  components: { BreadcrumbComponent },
};
</script>
