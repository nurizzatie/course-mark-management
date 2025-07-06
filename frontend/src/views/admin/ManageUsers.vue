<template>
  <AppLayout :role="'Admin'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <h2 class="mb-4 fw-bold">Manage Users</h2>

      <!-- ✅ Alert Notification -->
      <div v-if="alert.show" class="alert alert-dismissible fade show" :class="'alert-' + alert.type" role="alert">
        {{ alert.message }}
        <button type="button" class="btn-close" @click="alert.show = false"></button>
      </div>

      <!-- ✅ Create New User Form -->
      <div class="card p-4 mb-5">
        <h5 class="fw-bold mb-3">Create New User</h5>
        <div class="row g-2">
          <div class="col-md-3" v-for="(field, key) in fieldList" :key="key">
            <input
              v-if="key !== 'role' && key !== 'password'"
              v-model="newUser[key]"
              @blur="touched[key] = true"
              :class="{ 'form-control': true, 'is-invalid': touched[key] && newUser[key].trim() === '' }"
              :placeholder="field"
            />
            <input
              v-if="key === 'password'"
              v-model="newUser.password"
              type="password"
              @blur="touched.password = true"
              :class="{ 'form-control': true, 'is-invalid': touched.password && newUser.password.trim() === '' }"
              placeholder="Password"
            />
            <select
              v-if="key === 'role'"
              v-model="newUser.role"
              @blur="touched.role = true"
              :class="{ 'form-select': true, 'is-invalid': touched.role && newUser.role === '' }"
            >
              <option disabled value="">Select Role</option>
              <option>Admin</option>
              <option>Lecturer</option>
              <option>Advisor</option>
              <option>Student</option>
            </select>
            <div v-if="touched[key] && newUser[key].trim() === '' && key !== 'role'" class="invalid-feedback">
              {{ field }} is required.
            </div>
          </div>
          <div class="col-md-3">
            <button class="btn btn-success w-100" @click="createUser">Create User</button>
          </div>
        </div>
      </div>

      <!-- ✅ Grouped User Tables by Role -->
      <div v-for="role in ['Admin', 'Lecturer', 'Advisor', 'Student']" :key="role" class="mb-5">
        <h5 class="fw-bold">{{ role }}s</h5>
        <div v-if="filteredUsers(role).length">
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-fixed">
              <thead class="table-light">
                <tr>
                  <th>Name</th>
                  <th>Matric Number</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in filteredUsers(role)" :key="user.id">
                  <td>{{ user.name }}</td>
                  <td>{{ user.matric_number || '-' }}</td>
                  <td>{{ user.email }}</td>
                  <td>{{ user.role }}</td>
                  <td class="text-center">
                    <div class="d-flex justify-content-center gap-2">
                      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal" @click="openEditModal(user)">Edit</button>
                      <button class="btn btn-danger btn-sm" @click="deleteUser(user.id)">Delete</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div v-else class="text-muted">No {{ role.toLowerCase() }}s found.</div>
      </div>

      <!-- ✅ Edit User Role Modal -->
      <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editUserModalLabel">Edit User Role</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <p><strong>Name:</strong> {{ selectedUser.name }}</p>
              <p><strong>Email:</strong> {{ selectedUser.email }}</p>
              <label class="form-label">Select Role</label>
              <select v-model="selectedUser.tempRole" class="form-select">
                <option>Admin</option>
                <option>Lecturer</option>
                <option>Advisor</option>
                <option>Student</option>
              </select>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" data-bs-dismiss="modal" @click="saveRoleUpdate">Save Changes</button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script>
import axios from 'axios'
import AppLayout from '@/layouts/AppLayout.vue'
import adminNavItems from '@/constants/adminNavItems'


export default {
  name: 'ManageUsers',
  components: { AppLayout },
  data() {
    return {
      navItems: adminNavItems,
      pageTitle: 'Manage Users',
      users: [],
      selectedUser: {},
      newUser: {
        name: '',
        matric_number: '',
        email: '',
        password: '',
        role: ''
      },
      fieldList: {
        name: 'Name',
        matric_number: 'Matric Number',
        email: 'Email',
        password: 'Password',
        role: 'Role'
      },
      alert: {
        show: false,
        type: '',
        message: ''
      },
      touched: {
        name: false,
        matric_number: false,
        email: false,
        password: false,
        role: false
      }
    }
  },
  methods: {
    showAlert(type, message) {
      this.alert = { show: true, type, message }
      setTimeout(() => (this.alert.show = false), 3000)
    },
    isValidEmail(email) {
      const re = /\S+@\S+\.\S+/
      return re.test(email)
    },
    isValidForm() {
      const u = this.newUser
      return u.name && u.matric_number && this.isValidEmail(u.email) && u.password && u.role
    },
    markAllTouched() {
      for (const key in this.touched) this.touched[key] = true
    },
    fetchUsers() {
      axios.get('http://localhost:8080/api/admin/users')
        .then(res => {
          const rawUsers = Array.isArray(res.data) ? res.data : res.data.users
          this.users = rawUsers.map(user => ({ ...user, tempRole: user.role }))
        })
        .catch(() => this.showAlert('danger', '❌ Failed to load users'))
    },
    filteredUsers(role) {
      return this.users.filter(user => user.role?.toLowerCase() === role.toLowerCase())
    },
    createUser() {
      this.markAllTouched()
      if (!this.isValidForm()) {
        this.showAlert('danger', '❌ Please complete all fields correctly')
        return
      }
      axios.post('http://localhost:8080/api/admin/create-user', this.newUser)
        .then(() => {
          this.fetchUsers()
          this.newUser = { name: '', matric_number: '', email: '', password: '', role: '' }
          for (const key in this.touched) this.touched[key] = false
          this.showAlert('success', '✅ User created successfully')
        })
        .catch(() => this.showAlert('danger', '❌ Failed to create user'))
    },
    openEditModal(user) {
      this.selectedUser = JSON.parse(JSON.stringify(user))
    },
    saveRoleUpdate() {
      axios.put(`http://localhost:8080/api/admin/users/${this.selectedUser.id}/role`, {
        role: this.selectedUser.tempRole
      })
      .then(() => {
        this.showAlert('success', '✅ User role updated successfully')
        this.fetchUsers()
      })
      .catch(() => {
        this.showAlert('danger', '❌ Failed to update user role')
      })
    },
    deleteUser(id) {
      if (confirm('Are you sure you want to delete this user?')) {
        axios.delete(`http://localhost:8080/api/admin/users/${id}`)
          .then(() => {
            this.showAlert('success', '✅ User deleted successfully')
            this.fetchUsers()
          })
          .catch(() => {
            this.showAlert('danger', '❌ Failed to delete user')
          })
      }
    }
  },
  mounted() {
    this.fetchUsers()
  }
}
</script>

<style scoped>
.table {
  font-size: 0.95rem;
}

.table-fixed {
  table-layout: fixed;
  width: 100%;
}

.table-fixed th,
.table-fixed td {
  word-wrap: break-word;
  text-align: center;
  vertical-align: middle;
}
</style>