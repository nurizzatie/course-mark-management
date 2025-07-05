<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script>
export default {
  name: 'App'
}
</script>

<style>
/* Optional basic styling */
body {
  font-family: Arial, sans-serif;
  margin: 0;
  padding: 0;
}

html,
body,
#app {
  height: 100%;
  margin: 0;
}
</style>
