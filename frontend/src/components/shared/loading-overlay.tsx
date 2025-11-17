'use client';

import { JSX, useEffect, useState } from 'react';
import { useUIStore } from '@/modules/common/store/ui-store';

export const LoadingOverlay = (): JSX.Element | null => {
  const isLoading = useUIStore((state) => state.isLoading);
  const [progress, setProgress] = useState(isLoading ? 10 : 0);

  useEffect(() => {
    let timer: NodeJS.Timeout;

    if (isLoading) {
      timer = setInterval(() => {
        setProgress((old) => (old >= 90 ? old : old + Math.random() * 10));
      }, 300);
    } else {
      timer = setTimeout(() => {
        setProgress(100);
        setTimeout(() => {
          setProgress(0);
        }, 300);
      }, 0);
    }

    return () => {
      clearInterval(timer);
    };
  }, [isLoading]);

  if (progress === 0) return null;

  return (
    <div
      className="fixed top-0 start-0 h-1 bg-red-600 z-50 transition-all ease-out duration-300"
      style={{ width: `${progress}%` }}
    />
  );
};
