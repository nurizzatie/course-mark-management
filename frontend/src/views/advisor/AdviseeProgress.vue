<template>
    <AppLayout :role="'Advisor'" :navItems="navItems" :pageTitle="'Advisees Progress'">
        <div class="container py-4">
            <h5 class="fw-bold mb-4">📊 Advisee Progress: {{ studentName }}</h5>

            <div class="mt-4">
                <canvas id="overallChart" height="120"></canvas>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Select Course:</label>
                <select class="form-select w-auto" v-model="selectedCourse">
                    <option disabled value="">-- Choose Course --</option>
                    <option v-for="course in courseOptions" :key="course" :value="course">
                    {{ course }}
                    </option>
                </select>
            </div>

            <div v-if="selectedCourse" class="mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white fw-bold">{{ selectedCourse }}</div>
                    <div class="card-body table-responsive">
                    <table class="table table-bordered text-center">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Assessment</th>
                            <th>Type</th>
                            <th>Max</th>
                            <th>Weight %</th>
                            <th>Obtained Mark</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(item, idx) in selectedCourseData" :key="idx">
                            <td>{{ idx + 1 }}</td>
                            <td>{{ item.assessment_title }}</td>
                            <td>{{ item.assessment_type }}</td>
                            <td>{{ item.max_mark }}</td>
                            <td>{{ item.weight_percentage }}</td>
                            <td>{{ item.obtained_mark }}</td>
                        </tr>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);
import api from '@/api';

export default {
  name: 'AdviseeProgress',
  components: { AppLayout },
  data() {
    return {
      user: JSON.parse(localStorage.getItem('user')),
      selectedCourse: '',
      progress: [],
      studentName: '',
      overallChartInstance: null,
      navItems: [
        { name: 'Dashboard', link: '/advisor/dashboard' },
        { name: 'Advisees', link: '/advisor/students' },
        { name: 'Mark Review', link: '/advisor/reviews' },
        { name: 'Performance Analytics', link: '/advisor/analytics' },
        { name: 'Consultation', link: '/advisor/notes' },
        { name: 'Profile', link: '/advisor/profile' }
      ]
    };
  },
  computed: {
    groupedProgress() {
        const grouped = {};
        this.progress.forEach(item => {
        if (!grouped[item.course_name]) grouped[item.course_name] = [];
        grouped[item.course_name].push(item);
        });
        return grouped;
    },
    courseOptions() {
        return Object.keys(this.groupedProgress);
    },
    selectedCourseData() {
        return this.groupedProgress[this.selectedCourse] || [];
    },
    courseTotals() {
        const totals = {};

        this.progress.forEach(item => {
            if (!totals[item.course_name]) {
            totals[item.course_name] = {
                obtained: 0,
                totalWeight: 0
            };
            }

            const maxMark = parseFloat(item.max_mark);
            const obtained = parseFloat(item.obtained_mark);
            const weight = parseFloat(item.weight_percentage);

            const weightedScore = (obtained / maxMark) * weight;

            totals[item.course_name].obtained += weightedScore;
            totals[item.course_name].totalWeight += weight;
        });

        return totals;
    }
  },
  methods: {
    async fetchProgress() {
        const studentId = this.$route.params.id;
        try {
            const res = await api.get(`/advisor/advisee/${studentId}/progress`, {
            headers: {
                'X-User': JSON.stringify(this.user)
            }
            });
            this.progress = res.data;

            // Set default selected course after data is loaded
            if (this.courseOptions.length) {
            this.selectedCourse = this.courseOptions[0];
            }
            
            this.$nextTick(() => {
                this.renderOverallChart();
            });

            // Set student name (if available)
            if (res.data.length) {
            this.studentName = res.data[0].student_name || '';
            }

        } catch (err) {
            console.error('Failed to load advisee progress:', err);
        }
    },
    renderOverallChart() {
        const ctx = document.getElementById('overallChart');
        if (!ctx || !Object.keys(this.courseTotals).length) return;

        if (this.overallChartInstance) {
            this.overallChartInstance.destroy();
        }

        const labels = Object.keys(this.courseTotals);
        const data = labels.map(course => {
            const { obtained, totalWeight } = this.courseTotals[course];
            return (totalWeight > 0) ? (obtained / totalWeight) * 100 : 0; // Final % score
        });

        this.overallChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
            labels,
            datasets: [{
                label: 'Final Score (%)',
                data,
                backgroundColor: '#800000',
                borderColor: '#800000',
                borderWidth: 1
            }]
            },
            options: {
            plugins: {
                title: {
                display: true,
                text: 'Overall Course Performance'
                }
            },
            scales: {
                y: {
                beginAtZero: true,
                max: 100
                }
            }
            }
        });
        }
  },
  mounted() {
    this.fetchProgress();
  }
};
</script>