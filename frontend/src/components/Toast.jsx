import React, { useEffect } from 'react';

const typeStyles = {
  success: 'bg-green-600 text-white',
  warning: 'bg-orange-500 text-white',
  danger: 'bg-red-600 text-white',
  info: 'bg-blue-600 text-white',
};

export default function Toast({
  id,
  type = 'info',
  message,
  onClose,
  duration = 5000,
}) {
  useEffect(() => {
    const timer = setTimeout(() => {
      onClose(id);
    }, duration);
    return () => clearTimeout(timer);
  }, [id, onClose, duration]);

  return (
    <div
      className={`max-w-xs w-full shadow-lg rounded-md pointer-events-auto ring-1 ring-black ring-opacity-5
        ${typeStyles[type] || typeStyles.info}
        animate-fadeInDown
        mb-2
        px-4 py-3
        flex items-center space-x-3
      `}
      role="alert"
    >
      {/* Icône selon type */}
      <div>
        {type === 'success' && (
          <svg
            className="w-6 h-6"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              d="M5 13l4 4L19 7"
            />
          </svg>
        )}
        {type === 'warning' && (
          <svg
            className="w-6 h-6"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
        )}
        {type === 'danger' && (
          <svg
            className="w-6 h-6"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        )}
        {type === 'info' && (
          <svg
            className="w-6 h-6"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            viewBox="0 0 24 24"
          >
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="16" x2="12" y2="12" />
            <line x1="12" y1="8" x2="12" y2="8" />
          </svg>
        )}
      </div>

      <div className="flex-1 text-sm font-medium">{message}</div>

      <button
        onClick={() => onClose(id)}
        className="text-white hover:text-gray-200 focus:outline-none"
        aria-label="Close"
      >
        <svg
          className="w-5 h-5"
          fill="none"
          stroke="currentColor"
          strokeWidth="2"
          viewBox="0 0 24 24"
        >
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </button>
    </div>
  );
}
