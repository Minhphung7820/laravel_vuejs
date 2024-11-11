<template>
  <div class="product-form">
    <h2>{{ isEditMode ? 'Edit' : 'Create' }} Product</h2>

    <!-- Hiển thị vòng xoay loading trong khi chờ API -->
    <div v-if="isLoading" class="loading-container">
      <div class="spinner"></div>
    </div>

    <!-- Hiển thị form khi không loading -->
    <form v-else @submit.prevent="submitForm">
      <div class="form-group">
        <label>Name:</label>
        <input v-model="product.name" required />
      </div>

      <div class="form-group">
        <label>Description:</label>
        <!-- CKEditor field -->
        <CustomCKEditor v-model:modelValue="product.description" />
      </div>

      <div class="form-group">
        <label>Price:</label>
        <input type="number" v-model="product.price" required />
      </div>

      <div class="form-group">
        <label>Quantity:</label>
        <input type="number" v-model="product.quantity" required />
      </div>

      <!-- Input upload ảnh đại diện -->
      <div class="form-group">
        <label>Avatar Image:</label>
        <input type="file" @change="onAvatarChange" accept="image/*" />
        <div v-if="avatarPreview" class="image-preview">
          <img :src="avatarPreview" alt="Avatar Preview" />
          <button type="button" @click="removeAvatar" class="remove-button">X</button>
        </div>
      </div>

      <!-- Input upload nhiều ảnh -->
      <div class="form-group">
        <label>Gallery Images:</label>
        <input type="file" @change="onGalleryChange" multiple accept="image/*" />
        <div class="image-preview-container">
          <div v-for="(image, index) in galleryPreviews" :key="index" class="image-preview">
            <img :src="image" alt="Gallery Preview" />
            <button type="button" @click="removeGalleryImage(index)" class="remove-button">X</button>
          </div>
        </div>
      </div>

      <button type="submit" :disabled="isLoading || galleryUploading">
        {{ isEditMode ? 'Update' : 'Create' }}
      </button>
    </form>
  </div>
</template>

<script>
import CustomCKEditor from '../CKEditorCustom.vue';

export default {
  components: {
    CustomCKEditor
  },
  inject: ['$axios'],
  data() {
    return {
      product: {
        name: '',
        description: '',
        price: '',
        quantity: ''
      },
      isEditMode: false,
      isLoading: false,
      avatar: null,
      avatarPreview: null,
      avatarUrl: '',
      gallery: [],
      galleryPreviews: [],
      galleryUrls: [],
      galleryUploading: false // Trạng thái tải lên của ảnh trong gallery
    };
  },
  async created() {
    if (this.$route.params.id) {
      this.isEditMode = true;
      await this.fetchProduct();
    }
  },
  methods: {
    async onAvatarChange(event) {
      const file = event.target.files[0];
      if (this.validateImage(file)) {
        this.avatar = file;
        this.avatarPreview = URL.createObjectURL(file);
        this.avatarUrl = await this.uploadImage(file);
      } else {
        alert("Only JPEG, PNG, JPG, GIF files under 20MB are allowed.");
      }
    },
    async onGalleryChange(event) {
      const newFiles = Array.from(event.target.files);
      const validFiles = newFiles.filter(file => this.validateImage(file));
      if (validFiles.length !== newFiles.length) {
        alert("Some files are not valid. Only JPEG, PNG, JPG, GIF files under 20MB are allowed.");
      }

      this.galleryUploading = true; // Bắt đầu tải ảnh lên
      const newPreviews = validFiles.map(file => URL.createObjectURL(file));
      this.gallery.push(...validFiles);
      this.galleryPreviews.push(...newPreviews);

      // Sử dụng Promise.all để tải lên tất cả ảnh
      const uploadPromises = validFiles.map(file => this.uploadImage(file));
      const uploadedUrls = await Promise.all(uploadPromises);

      // Lưu các URL đã tải lên vào galleryUrls
      uploadedUrls.forEach(url => {
        this.galleryUrls.push({ url });
      });

      this.galleryUploading = false; // Hoàn tất tải ảnh
    },
    validateImage(file) {
      const allowedTypes = ["image/jpeg", "image/png", "image/jpg", "image/gif"];
      const maxSize = 20000000;
      return allowedTypes.includes(file.type) && file.size <= maxSize;
    },
    removeAvatar() {
      this.avatar = null;
      this.avatarPreview = null;
      this.avatarUrl = '';
    },
    removeGalleryImage(index) {
      this.gallery.splice(index, 1);
      this.galleryPreviews.splice(index, 1);
      this.galleryUrls.splice(index, 1);
    },
    async uploadImage(file) {
      const formData = new FormData();
      formData.append('image', file);
      try {
        const response = await this.$axios.post('/api/upload-image', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        return response.data.url;
      } catch (error) {
        console.error("Error uploading image:", error);
        return '';
      }
    },
    async fetchProduct() {
      this.isLoading = true;
      try {
        const response = await this.$axios.get(`/api/products/${this.$route.params.id}`);
        this.product = response.data;
        if (response.data.avatar) {
          this.avatarPreview = response.data.avatar;
          this.avatarUrl = response.data.avatar;
        }
        if (response.data.galleries) {
          this.galleryUrls = response.data.galleries.map(gallery => ({
            id: gallery.id,
            url: gallery.url
          }));
          this.galleryPreviews = this.galleryUrls.map(gallery => gallery.url);
        }
      } catch (error) {
        console.error("Error fetching product:", error);
      } finally {
        this.isLoading = false;
      }
    },
    async submitForm() {
      this.isLoading = true;
      try {
        const formData = {
          name: this.product.name,
          description: this.product.description,
          price: this.product.price,
          quantity: this.product.quantity,
          avatarUrl: this.avatarUrl,
          galleryUrls: this.galleryUrls
        };
        const request = this.isEditMode
          ? this.$axios.put(`/api/products/${this.$route.params.id}`, formData)
          : this.$axios.post('/api/products', formData);
        await request;
        this.$router.push('/products');
      } catch (error) {
        console.error("Error submitting form:", error);
      } finally {
        this.isLoading = false;
      }
    }
  }
};
</script>

<style scoped>
.product-form {
  padding: 20px;
  max-width: 600px;
  margin: 0 auto;
  background-color: #f5f5f5;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
h2 {
  color: #333;
  margin-bottom: 20px;
  text-align: center;
}
.form-group {
  margin-bottom: 15px;
}
label {
  font-weight: bold;
  display: block;
  margin-bottom: 5px;
}
input, textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 5px;
  box-sizing: border-box;
}
.ck-editor__editable_inline {
  min-height: 500px; /* Tăng chiều cao CKEditor */
}
button {
  background-color: #28a745;
  color: #fff;
  padding: 10px 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  display: block;
  width: 100%;
  font-size: 1.1rem;
  font-weight: bold;
  margin-top: 15px;
}
button:hover {
  background-color: #218838;
}
.image-preview-container {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}
.image-preview {
  position: relative;
  width: 100px;
  height: 100px;
  border: 1px solid #ddd;
  border-radius: 5px;
  overflow: hidden;
}
.image-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.remove-button {
  position: absolute;
  top: 0;
  right: 0;
  background-color: #e74c3c;
  color: white;
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  font-size: 14px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.2);
}
.remove-button:hover {
  background-color: #c0392b;
}
</style>