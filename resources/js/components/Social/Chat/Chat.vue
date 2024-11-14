<template>
  <div class="chat-container">
    <!-- Sidebar cho người dùng online -->
    <div class="users-online">
      <h3>Người dùng:</h3>
      <ul>
        <li v-for="user in users" :key="user.id">
          <strong>{{ user.name }}</strong>
          <span :class="{'online': user.online, 'offline': !user.online}">
            {{ user.online ? 'Đang online' : 'Offline' }}
          </span>
        </li>
      </ul>
    </div>

    <!-- Khung chat -->
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

      <!-- Khung nhập tin nhắn -->
      <input
        v-model="newMessage"
        @keydown="sendTypingEvent"
        @keyup.enter="sendMessage"
        placeholder="Nhập tin nhắn của bạn"
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
      users: [], // Danh sách người dùng với trạng thái online/offline
      messages: [],
      newMessage: '',
      userCurrent: null,
      isFriendTyping: false,
      isFriendTypingTimer: null,
    };
  },
  async mounted() {
    await this.getProfile();
    await this.getFriend();
    await this.getMessage();
    this.joinChannel();
  },
  beforeUnmount() {
    this.leaveChannel();
  },
  watch: {
    users: {
      handler() {
        this.sortUsers(); // Gọi hàm sắp xếp khi users thay đổi
      },
      deep: true // Cần có để theo dõi thay đổi bên trong từng phần tử của mảng
    }
  },
  methods: {
    async getFriend() {
      try {
        const getFriend = await this.$axios.get('/api/get-friend');
        this.users = getFriend.data.map(user => ({
          ...user,
          online: false // Khởi tạo tất cả người dùng là offline
        }));
      } catch (error) {
        console.error("Error:", error);
      }
    },
    async getMessage() {
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
          // Đánh dấu người dùng online từ danh sách ban đầu
          this.users.forEach(user => {
            user.online = onlineUsers.some(onlineUser => onlineUser.id === user.id);
          });
        })
        .joining((user) => {
          // Đánh dấu trạng thái online khi có người mới tham gia
          const targetUser = this.users.find(u => u.id === user.id);
          if (targetUser) targetUser.online = true;
        })
        .leaving((user) => {
          // Đánh dấu trạng thái offline khi có người rời khỏi
          const targetUser = this.users.find(u => u.id === user.id);
          if (targetUser) targetUser.online = false;
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
    sortUsers() {
      // Sắp xếp danh sách để người online ở đầu
      this.users.sort((a, b) => b.online - a.online);
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
          behavior: 'smooth'
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

.users-online ul {
  list-style-type: none;
  padding: 0;
}

.users-online li {
  margin-bottom: 8px;
}

.online {
  color: green;
  font-weight: bold;
  margin-left:10px ;
}

.offline {
  color: gray;
  margin-left:10px ;
}

.chat-box {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.messages {
  flex: 1;
  overflow-y: auto;
  max-height: 400px;
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
