<template>
  <div>
    <h2>Login</h2>
    <form @submit.prevent="login">
      <div>
        <label>Email:</label>
        <input type="email" v-model="email" required />
      </div>
      <div>
        <label>Password:</label>
        <input type="password" v-model="password" required />
      </div>
      <button type="submit">Login</button>
    </form>
    <p v-if="errorMessage">{{ errorMessage }}</p>
  </div>
</template>

<script>
export default {
  data() {
    return {
      email: '',
      password: '',
      errorMessage: '',
    };
  },
  methods: {
    async login() {
      try {
        const response = await this.$axios.post('/api/login', {
          email: this.email,
          password: this.password,
        });

        localStorage.setItem('token', response.data.token);
        this.$router.push('/dashboard');
      } catch (error) {
        this.errorMessage = 'Login failed. Please check your credentials.';
      }
    },
  },
};
</script>
