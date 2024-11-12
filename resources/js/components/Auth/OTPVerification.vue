<template>
  <div class="otp-container">
    <p>Please enter the OTP sent to your email.</p>
    <form @submit.prevent="verifyOTP" class="otp-form">
      <div class="form-group">
        <label>OTP Code:</label>
        <input type="text" v-model="otp" required />
      </div>
      <button type="submit" :disabled="isExpired" class="verify-button">
        Verify OTP
      </button>
      <p v-if="countdown !== null" class="countdown">Time left: {{ formattedTime }}</p>
      <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
    </form>
    <button @click="resendOTP" class="resend-button">
      Resend OTP
    </button>
  </div>
</template>

<script>
import moment from 'moment';

export default {
  inject: ['$axios'],
  data() {
    return {
      otp: '',
      errorMessage: '',
      countdown: null,
      isExpired: false,
      expiredAt: null,
      countdownInterval: null, // Biến để lưu interval
    };
  },
  computed: {
    formattedTime() {
      const minutes = Math.floor(this.countdown / 60);
      const seconds = Math.floor(this.countdown % 60); // Làm tròn số giây xuống
      return `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
    },
  },
  mounted() {
    const email = this.$route.query.email;
    if (email) {
      const storedExpiredAt = localStorage.getItem('otpExpiredAt');
      if (storedExpiredAt && moment(storedExpiredAt).isAfter(moment())) {
        this.expiredAt = moment(storedExpiredAt);
        this.startCountdown();
      } else {
        this.sendOTP(email);
      }
    } else {
      this.errorMessage = "Email không hợp lệ.";
    }
  },
  beforeDestroy() {
    // Xóa interval khi component bị hủy để tránh chạy tiếp tục
    clearInterval(this.countdownInterval);
  },
  methods: {
    async sendOTP(email) {
      try {
        const response = await this.$axios.post('/api/send-otp', { email });
        this.expiredAt = moment(response.data.expired_at);

        // Lưu expiredAt vào localStorage
        localStorage.setItem('otpExpiredAt', this.expiredAt.toISOString());

        this.startCountdown();
      } catch (error) {
        this.errorMessage = error.response.data.message;
      }
    },
    startCountdown() {
      // Xóa interval cũ nếu có
      if (this.countdownInterval) {
        clearInterval(this.countdownInterval);
      }

      this.countdown = moment.duration(this.expiredAt.diff(moment())).asSeconds();
      this.isExpired = false;

      // Tạo interval mới
      this.countdownInterval = setInterval(() => {
        if (this.countdown > 0) {
          this.countdown--;
        } else {
          this.isExpired = true;
          clearInterval(this.countdownInterval);
          localStorage.removeItem('otpExpiredAt'); // Xóa expiredAt khi hết hạn
        }
      }, 1000);
    },
    async verifyOTP() {
      try {
        await this.$axios.post('/api/verify-otp', { email: this.$route.query.email, otp: this.otp });
        localStorage.removeItem('otpExpiredAt'); // Xóa expiredAt khi xác thực thành công
        clearInterval(this.countdownInterval); // Xóa interval khi xác thực thành công
        this.$router.push('/'); // Điều hướng sau khi xác thực thành công
      } catch (error) {
        this.errorMessage = error.response.data.message;
      }
    },
    async resendOTP() {
      this.errorMessage = '';
      const email = this.$route.query.email;
      if (email) {
        localStorage.removeItem('otpExpiredAt'); // Xóa expiredAt cũ
        await this.sendOTP(email); // Gửi lại OTP và khởi tạo countdown mới
      }
    },
  },
};
</script>

<style scoped>
.otp-container {
  max-width: 400px;
  margin: 0 auto;
  padding: 20px;
  background-color: #f5f5f5;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

h2 {
  color: #333;
  text-align: center;
  margin-bottom: 10px;
}

p {
  text-align: center;
}

.otp-form {
  display: flex;
  flex-direction: column;
}

.form-group {
  margin-bottom: 15px;
}

label {
  font-weight: bold;
  margin-bottom: 5px;
  display: block;
}

input[type="text"] {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  box-sizing: border-box;
}

.verify-button, .resend-button {
  background-color: #3498db;
  color: white;
  border: none;
  padding: 10px 15px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 16px;
  font-weight: bold;
  margin-top: 10px;
  transition: background-color 0.3s;
}

.verify-button:hover, .resend-button:hover {
  background-color: #2980b9;
}

.countdown {
  font-size: 14px;
  color: #555;
  text-align: center;
  margin-top: 15px;
}

.error-message {
  color: #e74c3c;
  font-size: 14px;
  text-align: center;
  margin-top: 15px;
}
</style>
