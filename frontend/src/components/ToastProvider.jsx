// src/components/ToastProvider.jsx

import React, { createContext, useContext, useState, useCallback } from 'react'; // <-- Importez useCallback
import { v4 as uuidv4 } from 'uuid';
import Toast from './Toast';

// Création du contexte Toast
const ToastContext = createContext();

export function ToastProvider({ children }) {
  const [toasts, setToasts] = useState([]);

  const removeToast = useCallback((id) => {
    // <-- useCallback pour removeToast aussi
    setToasts((prev) => prev.filter((toast) => toast.id !== id));
  }, []); // removeToast dépend de setToasts, mais setToasts est garanti stable

  const addToast = useCallback(
    (message, type = 'info') => {
      // <-- useCallback pour addToast
      const id = uuidv4();
      setToasts((prev) => [...prev, { id, message, type }]);
      setTimeout(() => {
        removeToast(id);
      }, 5000);
    },
    [removeToast]
  ); // addToast dépend de removeToast et setToasts

  return (
    <ToastContext.Provider value={{ addToast, removeToast }}>
      {children}
      {/* C'est ici que nous rendons le conteneur des toasts et les toasts individuels */}
      <div
        aria-live="assertive"
        className="fixed inset-0 flex items-end px-4 py-6 pointer-events-none sm:p-6 sm:items-start"
        style={{ zIndex: 9999 }} // Assurez-vous que les toasts sont au-dessus de tout
      >
        <div className="w-full flex flex-col items-center space-y-2 sm:items-end">
          {/* Vérification cruciale : toasts est bien un tableau ici car géré par useState */}
          {toasts.map(({ id, type, message }) => (
            <Toast
              key={id}
              id={id}
              type={type}
              message={message}
              onClose={removeToast} // Passez la fonction removeToast pour que le Toast puisse se fermer
            />
          ))}
        </div>
      </div>
    </ToastContext.Provider>
  );
}

export function useToast() {
  const context = useContext(ToastContext);
  if (!context) {
    throw new Error('useToast doit être utilisé dans un ToastProvider');
  }
  return context;
}
