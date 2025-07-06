<template>
  <div class="max-w-md mx-auto">
    <h2 class="text-xl font-bold mb-4">My Profile</h2>
    <div class="p-4 border rounded shadow bg-white">
      <p><strong>Name:</strong> {{ profile.name }}</p>
      <p><strong>Email:</strong> {{ profile.email }}</p>
      <p><strong>Role:</strong> {{ profile.role }}</p>
    </div>
  </div>
</template>

<script>

import axios from 'axios';

export default {
  name: 'AdvisorProfileComponent',
  data() {
    return {
      profile: {}
    };
  },
  mounted() {
    axios.get('http://localhost/backend/public/api/profile')
      .then(res => {
        this.profile = res.data;
      })
      .catch(err => {
        console.error('Failed to load advisor profile:', err);
      });
  }
};
</script>

