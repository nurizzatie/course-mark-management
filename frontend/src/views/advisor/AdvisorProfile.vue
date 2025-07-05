<template>
  <div class="p-6 max-w-4xl mx-auto">
    <p style="color: green;">AdvisorProfile.vue is loaded</p>

    <!-- Back button -->
    <nav class="mb-4">
      <button
        @click="goBackToDashboard"
        class="bg-yellow-400 hover:bg-yellow-500 px-4 py-2 rounded"
      >
        ⬅ Back To Advisor Dashboard
      </button>
    </nav>

    <!-- Advisor Profile component -->
    <Profile />
  </div>
</template>

<script>
import Profile from '@/components/advisor/Profile.vue';

export default {
  name: 'AdvisorProfile',
  components: {
    Profile
  },
  methods: {
    goBackToDashboard() {
      this.$router.push({ name: 'AdvisorDashboard' });
    }
  },
  mounted() {
    console.log('AdvisorProfile.vue is mounted');
  }
};
</script>
