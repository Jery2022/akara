import React from 'react';
import ReactDOM from 'react-dom/client';
import './index.css';
//import 'bootstrap/dist/css/bootstrap.min.css';
import App from './App';
import { ToastProvider } from './components/ToastProvider';

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(
  <React.StrictMode>
    <ToastProvider>
      <App />
    </ToastProvider>
  </React.StrictMode>
);
