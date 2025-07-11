import React, { createContext, useContext, useState } from 'react';
import { v4 as uuidv4 } from 'uuid';

// Création du contexte Toast
const ToastContext = createContext();

// Provider qui gère les toasts
export function ToastProvider({ children }) {
  const [toasts, setToasts] = useState([]);

  // Ajouter un toast
  const addToast = (message, type = 'info') => {
    const id = uuidv4();
    setToasts((prev) => [...prev, { id, message, type }]);
  };

  // Supprimer un toast
  const removeToast = (id) => {
    setToasts((prev) => prev.filter((toast) => toast.id !== id));
  };

  return (
    <ToastContext.Provider value={{ toasts, addToast, removeToast }}>
      {children}
      {/* Ici vous pouvez afficher vos toasts, par exemple : */}
      <div className="toast-container">
        {toasts.map(({ id, message, type }) => (
          <div key={id} className={`toast toast-${type}`}>
            {message}
            <button onClick={() => removeToast(id)}>X</button>
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  );
}

// Hook personnalisé pour utiliser le contexte Toast
export function useToast() {
  const context = useContext(ToastContext);
  if (!context) {
    throw new Error('useToast doit être utilisé dans un ToastProvider');
  }
  return context;
}
