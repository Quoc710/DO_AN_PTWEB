/**
 * authApi.js - Quản lý gọi API cho module Tài Khoản
 */
const currentHost = (typeof window !== 'undefined' && window.location.hostname) ? window.location.hostname : 'localhost';
const API_BASE_URL = `http://${currentHost}:8000`;

function getAuthHeaders() {
  const token = typeof localStorage !== 'undefined' ? localStorage.getItem('aura_token') : null;
  return token ? { 'Authorization': `Bearer ${token}` } : {};
}

export const authApi = {
  /**
   * Đăng nhập nhân viên
   * @param {string} username - Tên tài khoản hoặc email
   * @param {string} password - Mật khẩu
   */
  async login(username, password) {
    const response = await fetch(`${API_BASE_URL}/api/tai-khoan/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      credentials: 'include', // Giữ PHP Session cookie
      body: JSON.stringify({ username, password }),
    });

    const data = await response.json();
    if (!response.ok || data.status === 'error') {
      throw new Error(data.message || 'Đăng nhập thất bại.');
    }

    if (data.data?.token && typeof localStorage !== 'undefined') {
      localStorage.setItem('aura_token', data.data.token);
    }

    return data;
  },

  /**
   * Đăng ký tài khoản nhân viên mới
   * @param {string} fullname - Họ và tên nhân viên
   * @param {string} username - Tên tài khoản
   * @param {string} password - Mật khẩu
   * @param {string} viTri - Vị trí làm việc
   */
  async register(fullname, username, password, viTri = 'Nhân viên bán hàng') {
    const response = await fetch(`${API_BASE_URL}/api/tai-khoan/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify({ fullname, username, password, viTri }),
    });

    const data = await response.json();
    if (!response.ok || data.status === 'error') {
      throw new Error(data.message || 'Đăng ký thất bại.');
    }

    if (data.data?.token && typeof localStorage !== 'undefined') {
      localStorage.setItem('aura_token', data.data.token);
    }

    return data;
  },

  /**
   * Lấy thông tin tài khoản hiện tại
   */
  async getProfile() {
    const response = await fetch(`${API_BASE_URL}/api/tai-khoan/me`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        ...getAuthHeaders(),
      },
      credentials: 'include',
    });

    const data = await response.json();
    if (!response.ok || data.status === 'error') {
      throw new Error(data.message || 'Chưa đăng nhập.');
    }
    return data;
  },

  /**
   * Đăng xuất
   */
  async logout() {
    const response = await fetch(`${API_BASE_URL}/api/tai-khoan/logout`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
      },
      credentials: 'include',
    });

    const data = await response.json();
    return data;
  },
};
