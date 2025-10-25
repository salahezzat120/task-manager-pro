export type TaskStatus = 'done' | 'missed' | 'due-today' | 'upcoming';

export function computeTaskStatus(dueDate: string, isCompleted: boolean): TaskStatus {
  if (isCompleted) {
    return 'done';
  }

  const today = new Date();
  today.setHours(0, 0, 0, 0);

  const due = new Date(dueDate);
  due.setHours(0, 0, 0, 0);

  if (due < today) {
    return 'missed';
  } else if (due.getTime() === today.getTime()) {
    return 'due-today';
  } else {
    return 'upcoming';
  }
}

export function getStatusColor(status: TaskStatus): string {
  switch (status) {
    case 'done':
      return 'bg-green-100 text-green-800 border-green-200';
    case 'missed':
      return 'bg-red-100 text-red-800 border-red-200';
    case 'due-today':
      return 'bg-yellow-100 text-yellow-800 border-yellow-200';
    case 'upcoming':
      return 'bg-blue-100 text-blue-800 border-blue-200';
    default:
      return 'bg-gray-100 text-gray-800 border-gray-200';
  }
}

export function getStatusLabel(status: TaskStatus): string {
  switch (status) {
    case 'done':
      return 'Done';
    case 'missed':
      return 'Missed/Late';
    case 'due-today':
      return 'Due Today';
    case 'upcoming':
      return 'Upcoming';
    default:
      return 'Unknown';
  }
}

export function getPriorityColor(priority: string): string {
  switch (priority) {
    case 'high':
      return 'text-red-600';
    case 'medium':
      return 'text-orange-600';
    case 'low':
      return 'text-blue-600';
    default:
      return 'text-gray-600';
  }
}
