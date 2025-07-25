<template>
  <AppLayout :role="'Admin'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <h2 class="mb-4 fw-bold">System Logs & Mark Updates</h2>

      <!-- Loading & Empty States -->
      <div v-if="loading" class="text-muted">Loading logs...</div>
      <div v-else-if="logs.length === 0" class="text-muted">No logs found.</div>

      <!-- Log Table -->
<div v-else class="table-responsive">
  <table class="table table-bordered table-striped">
    <thead class="table-light">
      <tr>
        <th>Date</th>
        <th>User</th>
        <th>Action</th>
        <th>Details</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="log in paginatedLogs" :key="log.id">
        <td>{{ formatDate(log.timestamp) }}</td>
        <td>{{ log.user_name || 'System' }}</td>
        <td>{{ log.action }}</td>
        <td>{{ log.details || '-' }}</td>
      </tr>
    </tbody>
  </table>

  <!-- Pagination Controls -->
  <nav v-if="totalPages > 1" class="mt-3">
    <ul class="pagination justify-content-center">
      <li class="page-item" :class="{ disabled: currentPage === 1 }">
        <button class="page-link" @click="currentPage--" :disabled="currentPage === 1">Previous</button>
      </li>
      <li
        v-for="page in totalPages"
        :key="page"
        class="page-item"
        :class="{ active: currentPage === page }"
      >
        <button class="page-link" @click="currentPage = page">{{ page }}</button>
      </li>
      <li class="page-item" :class="{ disabled: currentPage === totalPages }">
        <button class="page-link" @click="currentPage++" :disabled="currentPage === totalPages">Next</button>
      </li>
    </ul>
  </nav>
</div>


      <!-- Chart Section -->
      <div class="mt-5" v-if="logs.length > 0">
        <h5 class="fw-bold mb-3">Log Action Distribution</h5>
        <canvas id="logChart" ref="logChart" height="100"></canvas>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import axios from 'axios'
import AppLayout from '@/layouts/AppLayout.vue'
import adminNavItems from '@/constants/adminNavItems'
import Chart from 'chart.js/auto'

export default {
  name: 'SystemLogs',
  components: { AppLayout },
  data() {
    return {
      logs: [],
      chart: null,
      loading: true,
      navItems: adminNavItems,
      pageTitle: 'Logs',
      currentPage: 1,
      logsPerPage: 10
    }
  },
  computed: {
  paginatedLogs() {
    const start = (this.currentPage - 1) * this.logsPerPage
    const end = start + this.logsPerPage
    return this.logs.slice(start, end)
  },
  totalPages() {
    return Math.ceil(this.logs.length / this.logsPerPage)
  }
},

  methods: {
    fetchLogs() {
      this.loading = true
      axios.get(`${process.env.VUE_APP_API_URL}/api/admin/logs`)
        .then(res => {
          this.logs = res.data.logs || []
        })
        .catch(err => {
          console.error('Error fetching logs:', err)
          this.logs = []
        })
        .finally(() => {
          this.loading = false
        })
    },
    formatDate(timestamp) {
      return new Date(timestamp).toLocaleString()
    },
    renderChart() {
      if (this.chart) {
        this.chart.destroy()
      }

      const actionCounts = {}
      this.logs.forEach(log => {
        const action = log.action || 'Unknown'
        actionCounts[action] = (actionCounts[action] || 0) + 1
      })

      const labels = Object.keys(actionCounts)
      const values = Object.values(actionCounts)
      const colors = labels.map(() => this.getRandomColor())

      const ctx = this.$refs.logChart
      if (ctx) {
        this.chart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels,
            datasets: [{
              label: 'Log Count by Action',
              data: values,
              backgroundColor: colors
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: { display: false }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  stepSize: 1
                }
              }
            }
          }
        })
      }
    },
    getRandomColor() {
      const colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#20c997']
      return colors[Math.floor(Math.random() * colors.length)]
    }
  },
  watch: {
    logs() {
      if (this.logs.length > 0) {
        this.$nextTick(() => {
          this.renderChart()
        })
      }
    }
  },
  mounted() {
    this.fetchLogs()
  }
}
</script>

<style scoped>
.table {
  font-size: 0.95rem;
}
</style>
