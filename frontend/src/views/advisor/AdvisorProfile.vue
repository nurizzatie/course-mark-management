<template>
  <AppLayout :role="'Advisor'" :navItems="navItems" :pageTitle="'My Profile'">
    <div class="p-6 max-w-3xl mx-auto">
      <h2 class="text-2xl font-bold mb-4">👤 Advisor Profile</h2>

      <form @submit.prevent="updateProfile" class="space-y-4">
        <div>
          <label class="block font-medium">Name</label>
          <input v-model="form.name" class="w-full p-2 border rounded" required />
        </div>

        <div>
          <label class="block font-medium">Email</label>
          <input v-model="form.email" class="w-full p-2 border rounded" required />
        </div>

        <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
          Save Changes
        </button>
      </form>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
import api from '@/api';

export default {
  name: 'AdvisorProfile',
  components: { AppLayout },
  data() {
    return {
      form: { name: '', email: '' },
      navItems: [
        { name: 'Dashboard', link: '/advisor/dashboard' },
        { name: 'Student List', link: '/advisor/students' },
        { name: 'Review Marks', link: '/advisor/reviews' },
        { name: 'Performance Analytics', link: '/advisor/analytics' },
        { name: 'High-Risk Students', link: '/advisor/high-risk-students' },
        { name: 'Advisor Notes', link: '/advisor/notes' },
        { name: 'Profile', link: '/advisor/profile' }
      ]
    };
  },
  mounted() {
    const advisor = JSON.parse(localStorage.getItem('user'));
    if (advisor?.id) {
      api.get(`/advisor/profile/${advisor.id}`)
        .then(res => {
          this.form = res.data;
        })
        .catch(err => console.error('Failed to fetch profile:', err));
    }
  },
  methods: {
    async updateProfile() {
      const advisor = JSON.parse(localStorage.getItem('user'));
      try {
        await api.put(`/advisor/profile/${advisor.id}`, this.form);
        alert('Profile updated successfully');
      } catch (err) {
        alert('Failed to update profile');
      }
    }
  }
};
</script>


