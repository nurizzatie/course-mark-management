<template>
  <div>
    <!-- Desktop Sidebar -->
    <div class="d-none d-md-flex flex-column app-sidebar text-white">
      <div class="p-3">
       <div class="text-center mb-4">
          <img src="@/assets/app-logo2.jpg" class="rounded-circle" alt="CourseMark Logo" style="max-width: 100px;" />
       </div>

        <hr />
        <ul class="nav nav-pills flex-column mt-4">
          <li v-for="item in navItems" :key="item.name" class="nav-item">
            <router-link
              :to="item.link"
              class="nav-link"
              active-class="active"
            >
              {{ item.name }}
            </router-link>
          </li>
        </ul>
      </div>
    </div>

    <!-- Mobile Sidebar (Offcanvas) -->
    <div
      class="offcanvas offcanvas-start"
      tabindex="-1"
      id="mobileSidebar"
      aria-labelledby="mobileSidebarLabel"
      ref="mobileSidebar"
      style="background-color: #5D001D;"
    >
      <div class="offcanvas-header">
        <h5 class="offcanvas-title text-light" id="mobileSidebarLabel">CourseMark</h5>
        <hr />
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <ul class="nav nav-pills flex-column">
          <li v-for="item in navItems" :key="item.name" class="nav-item">
            <a
              href="#"
              class="nav-link"
              :class="{ active: $route.path === item.link }"
              @click.prevent="navigate(item.link)"
            >
              {{ item.name }}
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AppSidebar',
  props: {
    navItems: Array,
  },
  methods: {
    navigate(link) {
      // Close the offcanvas manually
      const sidebar = this.$refs.mobileSidebar;
      const bsOffcanvas = window.bootstrap.Offcanvas.getInstance(sidebar);
      if (bsOffcanvas) {
        bsOffcanvas.hide();
      }

      // Navigate using Vue Router
      if (this.$route.path !== link) {
        this.$router.push(link);
      }
    },
  },
}
</script>

<style scoped>
.app-sidebar {
  width: 250px;
  height: 100vh;
  background-color: #5D001D;
  position: sticky;
  top: 0;
}


.nav-link {
  color: white;
}

.nav-link:hover {
  background-color: #450016;
  color: #FBA704;
}

.nav-link.active {
  background-color: #FBA704;
  color: #5D001D !important;
  font-weight: bold;
}
</style>
