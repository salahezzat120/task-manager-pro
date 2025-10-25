// API service for Laravel backend
const API_BASE_URL = 'http://localhost:8000/api';

export interface User {
  id: number;
  email: string;
}

export interface Task {
  id: number;
  creator_id: number;
  assignee_id: number;
  title: string;
  description: string;
  due_date: string;
  priority: 'low' | 'medium' | 'high';
  is_completed: boolean;
  completed_at: string | null;
  created_at: string;
  updated_at: string;
  creator_email?: string;
  assignee_email?: string;
}

export interface AuthResponse {
  success: boolean;
  message: string;
  data: {
    user: User;
    token: string;
  };
}

export interface TasksResponse {
  success: boolean;
  data: Task[];
}

export interface TaskResponse {
  success: boolean;
  message?: string;
  data: Task;
}

class ApiService {
  private token: string | null = null;

  constructor() {
    // Load token from localStorage on initialization
    this.token = localStorage.getItem('auth_token');
  }

  private async request<T>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<T> {
    const url = `${API_BASE_URL}${endpoint}`;
    
    const config: RequestInit = {
      headers: {
        'Content-Type': 'application/json',
        ...(this.token && { Authorization: `Bearer ${this.token}` }),
        ...options.headers,
      },
      ...options,
    };

    try {
      const response = await fetch(url, config);
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || `HTTP error! status: ${response.status}`);
      }

      return data;
    } catch (error) {
      console.error('API request failed:', error);
      throw error;
    }
  }

  // Authentication methods
  async signUp(email: string, password: string): Promise<AuthResponse> {
    const response = await this.request<AuthResponse>('/auth/signup', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    });

    if (response.success) {
      this.token = response.data.token;
      localStorage.setItem('auth_token', this.token);
    }

    return response;
  }

  async signIn(email: string, password: string): Promise<AuthResponse> {
    const response = await this.request<AuthResponse>('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    });

    if (response.success) {
      this.token = response.data.token;
      localStorage.setItem('auth_token', this.token);
    }

    return response;
  }

  signOut(): void {
    this.token = null;
    localStorage.removeItem('auth_token');
  }

  async getCurrentUser(): Promise<User | null> {
    try {
      const response = await this.request<{ success: boolean; data: User }>('/auth/me');
      return response.data;
    } catch (error) {
      // If token is invalid, clear it
      this.signOut();
      return null;
    }
  }

  // Task methods
  async getTasks(): Promise<Task[]> {
    const response = await this.request<TasksResponse>('/tasks');
    return response.data;
  }

  async createTask(taskData: {
    title: string;
    description: string;
    due_date: string;
    priority: 'low' | 'medium' | 'high';
    assignee_email: string;
  }): Promise<Task> {
    const response = await this.request<TaskResponse>('/tasks', {
      method: 'POST',
      body: JSON.stringify(taskData),
    });
    return response.data;
  }

  async updateTask(id: number, taskData: Partial<Task>): Promise<Task> {
    const response = await this.request<TaskResponse>(`/tasks/${id}`, {
      method: 'PUT',
      body: JSON.stringify(taskData),
    });
    return response.data;
  }

  async deleteTask(id: number): Promise<void> {
    await this.request(`/tasks/${id}`, {
      method: 'DELETE',
    });
  }

  async toggleTaskComplete(id: number, completed: boolean): Promise<Task> {
    const response = await this.request<TaskResponse>(`/tasks/${id}/toggle-complete`, {
      method: 'POST',
      body: JSON.stringify({ is_completed: completed }),
    });
    return response.data;
  }

  // Check if user is authenticated
  isAuthenticated(): boolean {
    return !!this.token;
  }

  // Get stored token
  getToken(): string | null {
    return this.token;
  }

  // Get all users for assignment dropdown
  async getUsers(): Promise<User[]> {
    const response = await this.request<{ success: boolean; data: User[] }>('/users');
    return response.data;
  }
}

export const apiService = new ApiService();

