import React from 'react';
import { createRoot } from 'react-dom/client';
import '@xyflow/react/dist/style.css';
import ProcessStudioFlow from './ProcessStudioFlow';

const rootEl = document.getElementById('process-studio-root');

if (rootEl) {
  const initialFlow = rootEl.getAttribute('data-initial-flow') || '';
  const root = createRoot(rootEl);
  root.render(
    <React.StrictMode>
      <div className="w-full h-screen min-h-[800px] flex flex-col" style={{ width: '100%', height: '100vh', minHeight: '800px' }}>
        <ProcessStudioFlow initialFlowKey={initialFlow} />
      </div>
    </React.StrictMode>
  );
} else {
  console.warn('ProcessStudio root element #process-studio-root not found in DOM.');
}
