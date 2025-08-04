// frontend/src/utils/MessageDisplay.jsx

import React, { useEffect } from 'react';

function MessageDisplay({ message, onClose }) {
  useEffect(() => {
    if (message) {
      const timer = setTimeout(() => {
        onClose();
      }, 5000); // Le message disparaît après 5 secondes
      return () => clearTimeout(timer);
    }
  }, [message, onClose]);

  if (!message) return null;

  const bgColor = message.type === 'success' ? 'bg-green-500' : 'bg-red-500';
  const textColor = 'text-white';

  return (
    <div className={`fixed top-4 right-4 p-4 rounded-md shadow-lg ${bgColor} ${textColor} z-50`}>
      <div className="flex justify-between items-center">
        <span>{message.text}</span>
        <button onClick={onClose} className="ml-4 font-bold"> 
          &times;
        </button>
      </div>
    </div>
  );
}

export default MessageDisplay;