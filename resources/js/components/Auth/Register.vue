<template>
  <div>
    <h2>Register</h2>
    <form @submit.prevent="register">
      <div>
        <label>Name:</label>
        <input type="text" v-model="name" required />
      </div>
      <div>
        <label>Email:</label>
        <input type="email" v-model="email" required />
      </div>
      <div>
        <label>Password:</label>
        <input type="password" v-model="password" required />
      </div>
      <div>
        <label>Confirm Password:</label>
        <input type="password" v-model="passwordConfirmation" required />
      </div>
      <button type="submit">Register</button>
    </form>
    <p v-if="errorMessage">{{ errorMessage }}</p>
  </div>
</template>

<script>
export default {
  data() {
    return {
      name: '',
      email: '',
      password: '',
      passwordConfirmation: '',
      errorMessage: '',
    };
  },
  methods: {
    async register() {
      try {
        await this.$axios.post('/api/register', {
          name: this.name,
          email: this.email,
          password: this.password,
          password_confirmation: this.passwordConfirmation,
        });

        this.$router.push('/login');
      } catch (error) {
        this.errorMessage = 'Registration failed. Please try again.';
      }
    },
  },
};
</script>
