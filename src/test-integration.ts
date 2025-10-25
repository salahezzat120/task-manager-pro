// Test script to verify frontend-backend integration
import { apiService } from './lib/api';

export async function testIntegration() {
  console.log('🧪 Testing Frontend-Backend Integration...');

  try {
    // Test 1: API Status
    console.log('1. Testing API status...');
    const statusResponse = await fetch('http://localhost:8000/api/');
    const statusData = await statusResponse.json();
    console.log('✅ API Status:', statusData.message);

    // Test 2: User Login
    console.log('2. Testing user login...');
    const loginResponse = await apiService.signIn('test@example.com', 'password123');
    console.log('✅ Login successful:', loginResponse.data.user.email);

    // Test 3: Get Tasks
    console.log('3. Testing get tasks...');
    const tasks = await apiService.getTasks();
    console.log('✅ Tasks retrieved:', tasks.length, 'tasks');

    // Test 4: Create Task
    console.log('4. Testing create task...');
    const newTask = await apiService.createTask({
      title: 'Integration Test Task',
      description: 'This task was created during integration testing',
      due_date: '2024-12-31',
      priority: 'medium',
      assignee_email: 'test@example.com'
    });
    console.log('✅ Task created:', newTask.id);

    console.log('🎉 Integration test completed successfully!');
    return true;

  } catch (error) {
    console.error('❌ Integration test failed:', error);
    return false;
  }
}

// Run test if this file is executed directly
if (typeof window !== 'undefined') {
  testIntegration();
}

