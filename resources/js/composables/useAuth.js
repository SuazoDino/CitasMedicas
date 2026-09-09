import { ref, computed } from 'vue';
import axios from '../axios';

const user = ref(null);
const loading = ref(true);

export function useAuth() {
  const fetchUser = async () => {
    try {
      const token = localStorage.getItem('jwt_token');
      if (!token) {
        user.value = null;
        loading.value = false;
        return null;
      }
      
      const response = await axios.get('/me');
      user.value = response.data;
    } catch (error) {
      console.error('Error fetching user profile:', error);
      user.value = null;
      localStorage.removeItem('jwt_token');
    } finally {
      loading.value = false;
    }
    return user.value;
  };

  const logout = async () => {
    try {
      await axios.post('/logout');
    } catch (e) {
      console.error(e);
    } finally {
      user.value = null;
      localStorage.removeItem('jwt_token');
    }
  };

  return {
    user,
    loading,
    isAuthenticated: computed(() => !!user.value),
    isAdmin: computed(() => user.value && user.value.rol_id === 1),
    isPaciente: computed(() => user.value && user.value.rol_id === 2),
    isMedico: computed(() => user.value && user.value.rol_id === 3),
    fetchUser,
    logout
  };
}
