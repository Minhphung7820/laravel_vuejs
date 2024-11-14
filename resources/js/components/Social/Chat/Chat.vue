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
     <small v-if="isFriendTyping" class="text-gray-700">
            Someone is typing...
     </small>
     <br>
    <input v-model="newMessage" @keydown="sendTypingEvent" @keyup.enter="sendMessage" placeholder="Enter your message" />
  </div>
</template>

<script>
export default {
  inject: ['$axios'],
  data() {
    return {
      usersOnline: [], // Mảng để lưu danh sách người dùng đang online
      messages: [],
      newMessage: '',
      userCurrent : null,
      isFriendTyping : false,
      isFriendTypingTimer : null
    };
  },
  async mounted() {
    await this.getProfile()
    this.joinChannel();
  },
  beforeUnmount() {
    this.leaveChannel();
  },
  methods: {
   async getProfile()
      {
          try{
           const userProfile =  await this.$axios.get('/api/get-profile');
           this.userCurrent = userProfile;
          } catch (error) {
              console.error("Error:", error);
          }
      },
    joinChannel() {
      // Tham gia vào kênh room.1 và cập nhật danh sách người dùng
      Echo.join(`room.1`)
        .here((onlineUsers) => {
          this.usersOnline = onlineUsers;
          console.log('Danh sách người dùng hiện tại (here):', this.usersOnline);
        })
        .joining((user) => {
          this.usersOnline.push(user);
          console.log('Danh sách người dùng sau khi thêm (joining):', this.usersOnline);
        })
        .leaving((user) => {
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
            user: e.user.name,
            message: e.message
          });
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
    sendTypingEvent(){
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
    }
  }
};
</script>
