<template>
  <div>
    <h3>Người dùng đang online:</h3>
    <ul>
      <li v-for="user in usersOnline" :key="user.id">
        {{ user.name }}
      </li>
    </ul>

    <h3>Tin nhắn:</h3>
    <ul>
      <li v-for="msg in messages" :key="msg.id">
        {{ msg.user }}: {{ msg.message }}
      </li>
    </ul>

    <input v-model="newMessage" @keyup.enter="sendMessage" placeholder="Enter your message" />
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      usersOnline: [], // Mảng để lưu danh sách người dùng đang online
      messages: [],
      newMessage: ''
    };
  },
  mounted() {
    // Tham gia vào kênh room.1 và cập nhật danh sách người dùng
    Echo.join(`room.1`)
      .here((onlineUsers) => {
        // Cập nhật danh sách người dùng hiện tại khi vào kênh
        this.usersOnline = onlineUsers;
        console.log('Danh sách người dùng hiện tại (here):', this.usersOnline);
      })
      .joining((user) => {
        // Thêm người dùng vào danh sách khi có người tham gia
        this.usersOnline.push(user);
        console.log('Danh sách người dùng sau khi thêm (joining):', this.usersOnline);
      })
      .leaving((user) => {
        // Xóa người dùng khỏi danh sách khi có người rời đi
        this.usersOnline = this.usersOnline.filter(u => u.id !== user.id);
        console.log('Danh sách người dùng sau khi xóa (leaving):', this.usersOnline);
      })
      .error((error) => {
        console.error('Lỗi kết nối:', error);
      });

    // Nghe sự kiện MessageSent trong kênh chat
    Echo.channel('chat')
      .listen('MessageSent', (e) => {
        this.messages.push({
          user: e.user?.name,
          message: e.message
        });
      });
  },
  methods: {
    async sendMessage() {
      try {
        const response = await axios.post('/api/send-message', { message: this.newMessage });
        this.newMessage = '';
      } catch (error) {
        console.error("Error:", error);
      }
    }
  }
};
</script>
