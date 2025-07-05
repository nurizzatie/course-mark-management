<template>
  <div>
    <h1>Check Console for API response</h1>
  </div>
</template>

<script>
import api from '@/api';

export default {
  name: 'HelloWorld',
  mounted() {
    api.get('/hello')
      .then(response => console.log(response.data))
      .catch(error => console.error(error));
  }
}
</script>
