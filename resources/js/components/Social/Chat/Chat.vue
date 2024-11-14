<template>
  <div class="chat-container">
    <!-- Sidebar cho người dùng online -->
    <div class="users-online">
      <h3>Người dùng:</h3>
      <ul>
        <li v-for="user in formattedUser" :key="user.id">
          <strong>{{ user.name }}</strong>
          <span :class="{'online': user.online, 'offline': !user.online}">
            {{ user.online ? 'Đang online' : user.lastimeOnlineString }}
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
import { formatDistanceToNow } from 'date-fns';

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
      lastOnlineInterval: null // Khởi tạo biến lưu trữ interval
    };
  },
  async mounted() {
    await this.getProfile();
    await this.getFriend();
    await this.getMessage();
    this.joinChannel();
      // Tính thời gian chờ cho đến phút chẵn tiếp theo
    const now = new Date();
    const delay = (60 - now.getSeconds()) * 1000; // Thời gian chờ đến phút chẵn

    // Chờ đến phút chẵn đầu tiên
    setTimeout(() => {
      this.getFriend(); // Cập nhật lần đầu vào phút chẵn

      // Sau đó bắt đầu cập nhật mỗi phút
      this.lastOnlineInterval = setInterval(() => {
        this.getFriend();
      }, 60000); // 1 phút

    }, delay);
  },
  async beforeUnmount() {
    if (this.lastOnlineInterval) {
      clearInterval(this.lastOnlineInterval);
    }
    await this.setLasttime();
    this.leaveChannel();
  },
  watch: {
    users: {
      handler() {
        this.sortUsers();
      },
      deep: true
    }
  },
  computed:{
    formattedUser() {
        // Định dạng sản phẩm với giá thành
        const formattedUser = this.users.map(user => ({
          ...user,
          lastimeOnlineString: this.getLastTimeAgo(user.last_online)
        }));
        return formattedUser;
    },
  },
  methods: {
    async getFriend() {
      try {
        const getFriend = await this.$axios.get('/api/get-friend');
        this.users = getFriend.data.map(user => ({
          ...user,
          online: this.users.find(u => u.id === user.id)?.online || false // Giữ trạng thái online từ trước nếu có
        }));
      } catch (error) {
        console.error("Error:", error);
      }
    },
    async setLasttime() {
      try {
        await this.$axios.post('/api/set-last-time');
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
          // Đặt trạng thái online cho người dùng có mặt trong kênh khi trang được tải
          this.users.forEach(user => {
            user.online = onlineUsers.some(onlineUser => onlineUser.id === user.id);
          });
        })
        .joining((user) => {
          // Khi có người dùng mới tham gia kênh, đặt trạng thái online cho họ
          const targetUser = this.users.find(u => u.id === user.id);
          if (targetUser) targetUser.online = true;
        })
        .leaving((user) => {
          // Chỉ đặt trạng thái offline khi người dùng rời khỏi kênh
          const targetUser = this.users.find(u => u.id === user.id);
          if (targetUser) {
            targetUser.online = false;
          }
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
    },
    getLastTimeAgo(dateString) {
      const date = new Date(dateString);
      const timeAgo = formatDistanceToNow(date, { addSuffix: true }) === 'less than a minute ago' ? 'just now' :formatDistanceToNow(date, { addSuffix: true }) ;
      return `Online ${timeAgo}`;
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
