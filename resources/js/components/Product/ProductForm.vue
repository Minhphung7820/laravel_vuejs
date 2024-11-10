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
        <textarea v-model="product.description"></textarea>
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

      <button type="submit">{{ isEditMode ? 'Update' : 'Create' }}</button>
    </form>
  </div>
</template>

<script>
export default {
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
      avatarUrl: '', // URL của ảnh đại diện sau khi upload
      gallery: [],
      galleryPreviews: [], // Preview của gallery
      galleryUrls: [] // Mảng URL của các ảnh trong gallery sau khi upload (bao gồm id khi chỉnh sửa)
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

        // Upload ảnh đại diện và lưu link
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

      const newPreviews = validFiles.map(file => URL.createObjectURL(file));
      this.gallery.push(...validFiles);
      this.galleryPreviews.push(...newPreviews);

      // Upload ảnh gallery và lưu các link kèm id nếu có
      for (const file of validFiles) {
        const url = await this.uploadImage(file);
        this.galleryUrls.push({ url }); // Khi là file mới, không có `id`
      }
    },
    validateImage(file) {
      const allowedTypes = ["image/jpeg", "image/png", "image/jpg", "image/gif"];
      const maxSize = 20000000; // 20MB in bytes
      return allowedTypes.includes(file.type) && file.size <= maxSize;
    },
    removeAvatar() {
      this.avatar = null;
      this.avatarPreview = null;
      this.avatarUrl = ''; // Xóa link ảnh đại diện
    },
    removeGalleryImage(index) {
      this.gallery.splice(index, 1);
      this.galleryPreviews.splice(index, 1);
      this.galleryUrls.splice(index, 1); // Xóa link ảnh trong gallery
    },
    async uploadImage(file) {
      const formData = new FormData();
      formData.append('image', file);

      try {
        const response = await this.$axios.post('/api/upload-image', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        return response.data.url; // Giả sử API trả về link ảnh trong `url`
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

        // Điền dữ liệu avatar nếu có
        if (response.data.avatar) {
          this.avatarPreview = response.data.avatar;
          this.avatarUrl = response.data.avatar;
        }

        // Điền dữ liệu gallery nếu có
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
          avatarUrl: this.avatarUrl, // Gửi link ảnh đại diện
          galleryUrls: this.galleryUrls // Gửi link các ảnh trong gallery với id khi chỉnh sửa
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

/* Preview ảnh */
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
