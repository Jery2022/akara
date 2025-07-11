import Toast from './Toast';

export default function ToastContainer({ toasts, removeToast }) {
  return (
    <div
      aria-live="assertive"
      className="fixed inset-0 flex items-end px-4 py-6 pointer-events-none sm:p-6 sm:items-start"
      style={{ zIndex: 9999 }}
    >
      <div className="w-full flex flex-col items-center space-y-2 sm:items-end">
        {toasts.map(({ id, type, message }) => (
          <Toast
            key={id}
            id={id}
            type={type}
            message={message}
            onClose={removeToast}
          />
        ))}
      </div>
    </div>
  );
}
