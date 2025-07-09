<template>
  <AppLayout :role="'Admin'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <!-- Profile Info -->
      <div class="card mb-4 position-relative">
        <div class="card-header bg-dark text-white">
          <i class="fa-solid fa-address-card"></i> Admin Profile Information
        </div>

        <div class="card-body">
          <!-- Display Mode -->
          <div v-if="!isEditing">
            <p><strong>Name:</strong> {{ profile.name }}</p>
            <p><strong>Email:</strong> {{ profile.email }}</p>
            <p><strong>Matric No:</strong> {{ profile.matric_number }}</p>
          </div>

          <!-- Edit Mode -->
          <div v-else>
            <div class="mb-2">
              <label><strong>Name:</strong></label>
              <input v-model="editedProfile.name" class="form-control" />
            </div>
            <div class="mb-2">
              <label><strong>Email:</strong></label>
              <input v-model="editedProfile.email" class="form-control" />
            </div>
            <div class="mb-2">
              <label><strong>Matric No:</strong></label>
              <input
                v-model="editedProfile.matric_number"
                class="form-control"
                disabled
              />
            </div>
            
            <!-- Change Password -->
            <div class="mb-2">
              <label><strong>New Password:</strong></label>
              <input
                v-model="editedProfile.new_password"
                type="password"
                class="form-control"
              />
            </div>
            <div class="mb-2">
              <label><strong>Confirm New Password:</strong></label>
              <input
                v-model="editedProfile.confirm_password"
                type="password"
                class="form-control"
              />
            </div>
          </div>
          <br />
          <!-- Buttons -->
          <div class="position-absolute" style="bottom: 10px; right: 10px">
            <button
              v-if="!isEditing"
              class="btn btn-sm btn-warning"
              @click="startEditing"
            >
              <font-awesome-icon :icon="['fas', 'pencil-alt']" class="me-1" />
              Edit
            </button>

            <div v-else>
              <button class="btn btn-sm btn-success me-1" @click="saveProfile">
                <font-awesome-icon :icon="['fas', 'save']" class="me-1" />
                Save
              </button>
              <button class="btn btn-sm btn-secondary" @click="cancelEditing">
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from "@/layouts/AppLayout.vue";
import navItems from "@/constants/adminNavItems";
import api from "@/api";

export default {
  name: "AdminProfile",
  components: { AppLayout },
  data() {
    return {
      navItems,
      pageTitle: "My Profile",
      profile: {},
      editedProfile: {},
      isEditing: false,
    };
  },
  mounted() {
    this.fetchProfile();
  },
  methods: {
    fetchProfile() {
      const user = JSON.parse(localStorage.getItem("user"));
      if (!user?.id) {
        alert("User not found. Please login again.");
        return;
      }

      api
        .get(`/admin/profile`, {
          headers: { "X-User": JSON.stringify(user) },
        })
        .then((res) => {
          this.profile = res.data.profile;
        })
        .catch(() => {
          alert("Failed to load profile data");
        });
    },
    startEditing() {
      this.editedProfile = { ...this.profile };
      this.isEditing = true;
    },
    cancelEditing() {
      this.isEditing = false;
      this.editedProfile = {};
    },
    saveProfile() {
      const user = JSON.parse(localStorage.getItem("user"));
      api
        .put(`/admin/profile`, this.editedProfile, {
          headers: { "X-User": JSON.stringify(user) },
        })
        .then(() => {
          this.profile = { ...this.editedProfile };
          this.isEditing = false;
          alert("Profile updated successfully!");
        })
        .catch(() => {
          alert("Failed to update profile.");
        });
    },
  },
};
</script>
