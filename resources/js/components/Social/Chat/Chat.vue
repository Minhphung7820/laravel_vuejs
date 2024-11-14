<template>
  <div class="chat-container">
    <!-- Sidebar for online users -->
    <div class="users-online">
      <h3>Người dùng đang online:</h3>
      <ul>
        <li v-for="user in usersOnline" :key="user.id">
          {{ user.name }}
        </li>
      </ul>
    </div>

    <!-- Chat box -->
    <div class="chat-box">
      <h3>Tin nhắn:</h3>
      <div class="messages" ref="messagesContainer">
        <ul>
          <li v-for="msg in messages" :key="msg.id">
            <strong>{{ msg.user.name }}:</strong> {{ msg.message }}
          </li>
        </ul>
        <small v-if="isFriendTyping" class="text-gray-700">
          Someone is typing...
        </small>
      </div>

      <!-- Message input box -->
      <input
        v-model="newMessage"
        @keydown="sendTypingEvent"
        @keyup.enter="sendMessage"
        placeholder="Enter your message"
        class="message-input"
      />
    </div>
  </div>
</template>

<script>
export default {
  inject: ['$axios'],
  data() {
    return {
      usersOnline: [],
      messages: [],
      newMessage: '',
      userCurrent: null,
      isFriendTyping: false,
      isFriendTypingTimer: null,
    };
  },
  async mounted() {
    await this.getProfile();
    await this.getMessage();
    this.joinChannel();
  },
  beforeUnmount() {
    this.leaveChannel();
  },
  methods: {
    async getMessage(){
      try {
        const getMessage = await this.$axios.get('/api/get-message');
        this.messages = getMessage.data;
      } catch (error) {
        console.error("Error:", error);
      }
    },
    async getProfile() {
      try {
        const userProfile = await this.$axios.get('/api/get-profile');
        this.userCurrent = userProfile;
      } catch (error) {
        console.error("Error:", error);
      }
    },
    joinChannel() {
      Echo.join(`room.1`)
        .here((onlineUsers) => {
          this.usersOnline = onlineUsers;
        })
        .joining((user) => {
          this.usersOnline.push(user);
        })
        .leaving((user) => {
          this.usersOnline = this.usersOnline.filter(u => u.id !== user.id);
        })
        .error((error) => {
          console.error('Lỗi kết nối:', error);
        });

      Echo.channel('chat')
        .listen('MessageSent', (e) => {
          this.messages.push({
            user: e.user,
            message: e.message
          });
          this.scrollToBottom();
        });

      Echo.join(`room.1`)
        .listenForWhisper("typing", (e) => {
          this.isFriendTyping = e.userID !== this.userCurrent.data.id;
          if (this.isFriendTypingTimer) {
            clearTimeout(this.isFriendTypingTimer);
          }
          this.isFriendTypingTimer = setTimeout(() => {
            this.isFriendTyping = false;
          }, 1000);
        });
    },
    sendTypingEvent() {
      Echo.join(`room.1`)
        .whisper("typing", {
          userID: this.userCurrent.data.id,
        });
    },
    leaveChannel() {
      Echo.leave(`room.1`);
    },
    async sendMessage() {
      try {
        await this.$axios.post('/api/send-message', { message: this.newMessage });
        this.newMessage = '';
      } catch (error) {
        console.error("Error:", error);
      }
    },
    scrollToBottom() {
      this.$nextTick(() => {
        const container = this.$refs.messagesContainer;
        container.scrollTo({
          top: container.scrollHeight,
          behavior: 'smooth' // This enables smooth scrolling
        });
      });
    }
  }
};
</script>

<style scoped>
.chat-container {
  display: flex;
  gap: 20px;
}

.users-online {
  width: 200px;
  background: #f4f4f4;
  padding: 15px;
  border-radius: 8px;
  height: 500px;
  overflow-y: auto;
}

.chat-box {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.messages {
  flex: 1;
  overflow-y: auto;
  max-height: 400px; /* Set max-height to keep it fixed and allow scrolling */
  border: 1px solid #ccc;
  padding: 15px;
  margin-bottom: 10px;
  border-radius: 8px;
  background-color: #f9f9f9;
}

.message-input {
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #ccc;
  width: 100%;
}
</style>
