<template>
  <AppLayout :role="'Student'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <!-- Profile Info -->
      <div class="card mb-4 position-relative">
        <div class="card-header bg-dark text-white">
          <i class="fa-solid fa-address-card"></i> Profile Information
        </div>

        <div class="card-body">
          <!-- Display Mode -->
          <div v-if="!isEditing">
            <p><strong>Name:</strong> {{ profile.name }}</p>
            <p><strong>Email:</strong> {{ profile.email }}</p>
            <p><strong>Matric No:</strong> {{ profile.matric_number }}</p>
            <p><strong>Semester:</strong> {{ profile.semester }}</p>
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
            <div class="mb-2">
              <label><strong>Semester:</strong></label>
              <input :value="profile.semester" class="form-control" disabled />
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

      <!-- Enrolled Courses -->
      <div class="card">
        <div class="card-header bg-dark text-white">
          <i class="fa-solid fa-book"></i> Enrolled Courses
        </div>
        <div class="card-body table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="table-dark">
              <tr class="text-center">
                <th>#</th>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Semester</th>
                <th>Year</th>
              </tr>
            </thead>
            <tbody>
              <tr
                class="text-center"
                v-for="(course, index) in courses"
                :key="index"
              >
                <td>{{ index + 1 }}</td>
                <td>{{ course.course_code }}</td>
                <td>{{ course.course_name }}</td>
                <td>{{ course.semester }}</td>
                <td>{{ course.year }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from "@/layouts/AppLayout.vue";
import api from "@/api";

export default {
  name: "StudentProfile",
  components: { AppLayout },
  data() {
    return {
      pageTitle: "My Profile",
      navItems: [
        { name: "Dashboard", link: "/student/dashboard" },
        { name: "Performance Chart", link: "/student/performance" },
      ],
      profile: {},
      editedProfile: {},
      courses: [],
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
        .get(`/student/${user.id}/profile`, {
          headers: { "X-User": JSON.stringify(user) },
        })
        .then((res) => {
          this.profile = res.data.profile;
          this.courses = res.data.courses;
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

      if (
        this.editedProfile.new_password &&
        this.editedProfile.new_password !== this.editedProfile.confirm_password
      ) {
        alert("Passwords do not match.");
        return;
      }

      const payload = {
        name: this.editedProfile.name,
        email: this.editedProfile.email,
      };

      if (this.editedProfile.new_password) {
        payload.password = this.editedProfile.new_password;
      }

      api
        .put(`/admin/profile`, payload, {
          headers: { "X-User": JSON.stringify(user) },
        })
        .then(() => {
          this.profile = { ...this.profile, ...payload };
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
