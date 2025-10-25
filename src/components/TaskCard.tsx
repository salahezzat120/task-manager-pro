import { Calendar, Edit, Trash2, CheckCircle2, Circle, AlertTriangle } from 'lucide-react';
import { computeTaskStatus, getStatusColor, getStatusLabel, getPriorityColor } from '../utils/taskStatus';

import { Task } from '../lib/api';

interface TaskCardProps {
  task: Task;
  onEdit: (task: Task) => void;
  onDelete: (id: number) => void;
  onToggleComplete: (id: number, completed: boolean) => void;
}

export function TaskCard({ task, onEdit, onDelete, onToggleComplete }: TaskCardProps) {
  const status = computeTaskStatus(task.due_date, task.is_completed);
  const statusColor = getStatusColor(status);
  const statusLabel = getStatusLabel(status);

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    });
  };

  return (
    <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:shadow-md transition-all duration-200">
      <div className="flex items-start justify-between gap-4 mb-3">
        <div className="flex-1 min-w-0">
          <div className="flex items-center gap-2 mb-2">
            <span className={`px-3 py-1 rounded-full text-xs font-medium border ${statusColor}`}>
              {statusLabel}
            </span>
            <span className={`text-xs font-medium uppercase tracking-wide ${getPriorityColor(task.priority)}`}>
              {task.priority}
            </span>
          </div>
          <h3 className="text-lg font-semibold text-slate-900 mb-1 break-words">
            {task.title}
          </h3>
          {task.description && (
            <p className="text-sm text-slate-600 line-clamp-2 break-words">
              {task.description}
            </p>
          )}
        </div>
        <button
          onClick={() => onToggleComplete(task.id, !task.is_completed)}
          className="flex-shrink-0 p-2 hover:bg-slate-50 rounded-lg transition"
          title={task.is_completed ? 'Mark as incomplete' : 'Mark as complete'}
        >
          {task.is_completed ? (
            <CheckCircle2 className="w-6 h-6 text-green-600" />
          ) : (
            <Circle className="w-6 h-6 text-slate-400" />
          )}
        </button>
      </div>

      <div className="flex items-center justify-between pt-3 border-t border-slate-100">
        <div className="flex items-center gap-2 text-sm text-slate-600">
          <Calendar className="w-4 h-4" />
          <span>{formatDate(task.due_date)}</span>
          {status === 'missed' && (
            <AlertTriangle className="w-4 h-4 text-red-500 ml-1" />
          )}
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={() => onEdit(task)}
            className="p-2 hover:bg-slate-100 rounded-lg transition text-slate-600 hover:text-slate-900"
            title="Edit task"
          >
            <Edit className="w-4 h-4" />
          </button>
          <button
            onClick={() => onDelete(task.id)}
            className="p-2 hover:bg-red-50 rounded-lg transition text-slate-600 hover:text-red-600"
            title="Delete task"
          >
            <Trash2 className="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>
  );
}
