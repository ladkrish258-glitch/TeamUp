import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { spawn } from 'child_process';
import net from 'net';

function autoBackendPlugin(port) {
  let child = null;
  return {
    name: 'auto-backend-server',
    configureServer() {
      const sock = new net.Socket();
      sock.setTimeout(300);
      sock.once('connect', () => sock.destroy());
      sock.once('error', () => {
        sock.destroy();
        if (!child) {
          child = spawn('node', ['../server/index.js'], { stdio: 'inherit' });
        }
      });
      sock.once('timeout', () => {
        sock.destroy();
        if (!child) {
          child = spawn('node', ['../server/index.js'], { stdio: 'inherit' });
        }
      });
      sock.connect(port, '127.0.0.1');
    }
  };
}

export default defineConfig({
  plugins: [react(), autoBackendPlugin(5001)],
  server: {
    port: 5175,
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:5001',
        changeOrigin: true
      }
    }
  }
});
