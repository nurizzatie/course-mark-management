<template>
  <div class="card text-white mb-3" :class="bgClass" style="max-width: 30rem;">
    <div class="card-header fw-bold">{{ title }}</div>
    <div class="card-body">
      <h5 class="card-title fs-1 fw-bolder mx-3">{{ value }}</h5>
      <p class="card-text">{{ subtitle }}</p> 
    </div>
  </div>
</template>

<script>
export default {
  name: 'StatCard',
  props: {
    title: String,
    value: String,
    subtitle: String,
    bgClass: {
      type: String,
      default: 'bg-info'
    }
  }
}
</script>
