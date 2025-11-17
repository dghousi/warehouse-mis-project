'use client';

import * as React from 'react';
import { JSX } from 'react';
import Image from 'next/image';
import { cn } from '@/lib/utilities';
import { Slot } from '@radix-ui/react-slot';
import {
  FileArchiveIcon,
  FileAudioIcon,
  FileCodeIcon,
  FileCogIcon,
  FileIcon,
  FileTextIcon,
  FileVideoIcon,
} from 'lucide-react';

const ROOT_NAME = 'FileUpload';
const DROPZONE_NAME = 'FileUploadDropzone';
const TRIGGER_NAME = 'FileUploadTrigger';
const LIST_NAME = 'FileUploadList';
const ITEM_NAME = 'FileUploadItem';
const ITEM_PREVIEW_NAME = 'FileUploadItemPreview';
const ITEM_METADATA_NAME = 'FileUploadItemMetadata';
const ITEM_PROGRESS_NAME = 'FileUploadItemProgress';
const ITEM_DELETE_NAME = 'FileUploadItemDelete';
const CLEAR_NAME = 'FileUploadClear';

const useLazyRef = <T,>(fn: () => T): React.RefObject<T> => {
  const ref = React.useRef<T | null>(null);

  ref.current ??= fn();

  return ref as React.RefObject<T>;
};

type Direction = 'ltr' | 'rtl';

const DirectionContext = React.createContext<Direction | undefined>(undefined);

function useDirection(dirProp?: Direction): Direction {
  const contextDir = React.useContext(DirectionContext);
  return dirProp ?? contextDir ?? 'ltr';
}

interface FileState {
  file: File;
  progress: number;
  error?: string;
  status: 'idle' | 'uploading' | 'error' | 'success';
}

interface StoreState {
  files: Map<File, FileState>;
  dragOver: boolean;
  invalid: boolean;
}

type StoreAction =
  | { type: 'ADD_FILES'; files: File[] }
  | { type: 'SET_FILES'; files: File[] }
  | { type: 'SET_PROGRESS'; file: File; progress: number }
  | { type: 'SET_SUCCESS'; file: File }
  | { type: 'SET_ERROR'; file: File; error: string }
  | { type: 'REMOVE_FILE'; file: File }
  | { type: 'SET_DRAG_OVER'; dragOver: boolean }
  | { type: 'SET_INVALID'; invalid: boolean }
  | { type: 'CLEAR' };

type Store = {
  dispatch: (action: StoreAction) => void;
  getState: () => StoreState;
  subscribe: (listener: () => void) => () => boolean;
};

const createStore = (
  listeners: Set<() => void>,
  files: Map<File, FileState>,
  urlCache: WeakMap<File, string>,
  invalid: boolean,
  onValueChange?: (files: File[]) => void
): Store => {
  let state: StoreState = {
    dragOver: false,
    files,
    invalid: invalid,
  };

  const reducer = (state: StoreState, action: StoreAction): StoreState => {
    switch (action.type) {
      case 'ADD_FILES': {
        for (const file of action.files) {
          files.set(file, {
            file,
            progress: 0,
            status: 'idle',
          });
        }

        if (onValueChange) {
          const fileList = Array.from(files.values()).map((fileState) => fileState.file);
          onValueChange(fileList);
        }
        return { ...state, files };
      }

      case 'SET_FILES': {
        const newFileSet = new Set(action.files);
        for (const existingFile of files.keys()) {
          if (!newFileSet.has(existingFile)) {
            files.delete(existingFile);
          }
        }

        for (const file of action.files) {
          const existingState = files.get(file);
          if (!existingState) {
            files.set(file, {
              file,
              progress: 0,
              status: 'idle',
            });
          }
        }
        return { ...state, files };
      }

      case 'SET_PROGRESS': {
        const fileState = files.get(action.file);
        if (fileState) {
          files.set(action.file, {
            ...fileState,
            progress: action.progress,
            status: 'uploading',
          });
        }
        return { ...state, files };
      }

      case 'SET_SUCCESS': {
        const fileState = files.get(action.file);
        if (fileState) {
          files.set(action.file, {
            ...fileState,
            progress: 100,
            status: 'success',
          });
        }
        return { ...state, files };
      }

      case 'SET_ERROR': {
        const fileState = files.get(action.file);
        if (fileState) {
          files.set(action.file, {
            ...fileState,
            error: action.error,
            status: 'error',
          });
        }
        return { ...state, files };
      }

      case 'REMOVE_FILE': {
        if (urlCache) {
          const cachedUrl = urlCache.get(action.file);
          if (cachedUrl) {
            URL.revokeObjectURL(cachedUrl);
            urlCache.delete(action.file);
          }
        }

        files.delete(action.file);

        if (onValueChange) {
          const fileList = Array.from(files.values()).map((fileState) => fileState.file);
          onValueChange(fileList);
        }
        return { ...state, files };
      }

      case 'SET_DRAG_OVER': {
        return { ...state, dragOver: action.dragOver };
      }

      case 'SET_INVALID': {
        return { ...state, invalid: action.invalid };
      }

      case 'CLEAR': {
        if (urlCache) {
          for (const file of files.keys()) {
            const cachedUrl = urlCache.get(file);
            if (cachedUrl) {
              URL.revokeObjectURL(cachedUrl);
              urlCache.delete(file);
            }
          }
        }

        files.clear();
        if (onValueChange) {
          onValueChange([]);
        }
        return { ...state, files, invalid: false };
      }

      default:
        return state;
    }
  };

  const getState = (): StoreState => {
    return state;
  };

  const dispatch = (action: StoreAction): void => {
    state = reducer(state, action);
    for (const listener of listeners) {
      listener();
    }
  };

  const subscribe = (listener: () => void): (() => boolean) => {
    listeners.add(listener);
    return () => listeners.delete(listener);
  };

  return { dispatch, getState, subscribe };
};

const StoreContext = React.createContext<ReturnType<typeof createStore> | null>(null);

const useStoreContext = (consumerName: string): ReturnType<typeof createStore> => {
  const context = React.useContext(StoreContext);
  if (!context) {
    throw new Error(`\`${consumerName}\` must be used within \`${ROOT_NAME}\``);
  }
  return context;
};

const useStore = <T,>(selector: (state: StoreState) => T): T => {
  const store = useStoreContext(ROOT_NAME);

  const lastValueRef = useLazyRef<{ value: T; state: StoreState } | null>(() => null);

  const getSnapshot = React.useCallback(() => {
    const state = store.getState();
    const prevValue = lastValueRef.current;

    if (prevValue && prevValue.state === state) {
      return prevValue.value;
    }

    const nextValue = selector(state);
    lastValueRef.current = { state, value: nextValue };
    return nextValue;
  }, [store, selector, lastValueRef]);

  return React.useSyncExternalStore(store.subscribe, getSnapshot, getSnapshot);
};

interface FileUploadContextValue {
  inputId: string;
  dropzoneId: string;
  listId: string;
  labelId: string;
  disabled: boolean;
  dir: Direction;
  inputRef: React.RefObject<HTMLInputElement | null>;
  urlCache: WeakMap<File, string>;
  onFilesChange: (files: File[]) => void;
}

const FileUploadContext = React.createContext<FileUploadContextValue | null>(null);

const useFileUploadContext = (consumerName: string): FileUploadContextValue => {
  const context = React.useContext(FileUploadContext);
  if (!context) {
    throw new Error(`\`${consumerName}\` must be used within \`${ROOT_NAME}\``);
  }
  return context;
};

interface FileUploadRootProps
  extends Omit<React.ComponentProps<'div'>, 'defaultValue' | 'onChange'> {
  value?: File[];
  defaultValue?: File[];
  onValueChange?: (files: File[]) => void;
  onAccept?: (files: File[]) => void;
  onFileAccept?: (file: File) => void;
  onFileReject?: (file: File, message: string) => void;
  onFileValidate?: (file: File) => string | null | undefined;
  onUpload?: (
    files: File[],
    options: {
      onProgress: (file: File, progress: number) => void;
      onSuccess: (file: File) => void;
      onError: (file: File, error: Error) => void;
    }
  ) => Promise<void> | void;
  accept?: string;
  maxFiles?: number;
  maxSize?: number;
  dir?: Direction;
  label?: string;
  name?: string;
  asChild?: boolean;
  disabled?: boolean;
  invalid?: boolean;
  multiple?: boolean;
  required?: boolean;
}

const FileUploadRoot = (props: FileUploadRootProps): JSX.Element => {
  const {
    accept,
    asChild,
    children,
    className,
    defaultValue,
    dir: dirProp,
    disabled = false,
    invalid = false,
    label,
    maxFiles,
    maxSize,
    multiple = false,
    name,
    onAccept,
    onFileAccept,
    onFileReject,
    onFileValidate,
    onUpload,
    onValueChange,
    required = false,
    value,
    ...rootProps
  } = props;

  const inputId = React.useId();
  const dropzoneId = React.useId();
  const listId = React.useId();
  const labelId = React.useId();

  const dir = useDirection(dirProp);
  const listeners = useLazyRef(() => new Set<() => void>()).current;
  const files = useLazyRef<Map<File, FileState>>(() => new Map()).current;
  const urlCache = useLazyRef(() => new WeakMap<File, string>()).current;
  const inputRef = React.useRef<HTMLInputElement>(null);
  const isControlled = value !== undefined;

  const store = React.useMemo(
    () => createStore(listeners, files, urlCache, invalid, onValueChange),
    [listeners, files, invalid, onValueChange, urlCache]
  );

  const acceptTypes = React.useMemo(
    () => accept?.split(',').map((t) => t.trim()) ?? null,
    [accept]
  );

  const onProgress = useLazyRef(() => {
    let frame = 0;
    return (file: File, progress: number) => {
      if (frame) return;
      frame = requestAnimationFrame(() => {
        frame = 0;
        store.dispatch({
          file,
          progress: Math.min(Math.max(0, progress), 100),
          type: 'SET_PROGRESS',
        });
      });
    };
  }).current;

  React.useEffect(() => {
    if (isControlled) {
      store.dispatch({ files: value, type: 'SET_FILES' });
    } else if (defaultValue && defaultValue.length > 0 && !store.getState().files.size) {
      store.dispatch({ files: defaultValue, type: 'SET_FILES' });
    }
  }, [value, defaultValue, isControlled, store]);

  React.useEffect(() => {
    return () => {
      for (const file of files.keys()) {
        const cachedUrl = urlCache.get(file);
        if (cachedUrl) {
          URL.revokeObjectURL(cachedUrl);
        }
      }
    };
  }, [files, urlCache]);

  const onFilesUpload = React.useCallback(
    async (files: File[]) => {
      try {
        for (const file of files) {
          store.dispatch({ file, progress: 0, type: 'SET_PROGRESS' });
        }

        if (onUpload) {
          await onUpload(files, {
            onError: (file, error) => {
              store.dispatch({
                error: error.message ?? 'Upload failed',
                file,
                type: 'SET_ERROR',
              });
            },
            onProgress,
            onSuccess: (file) => {
              store.dispatch({ file, type: 'SET_SUCCESS' });
            },
          });
        } else {
          for (const file of files) {
            store.dispatch({ file, type: 'SET_SUCCESS' });
          }
        }
      } catch (error) {
        const errorMessage = error instanceof Error ? error.message : 'Upload failed';
        for (const file of files) {
          store.dispatch({
            error: errorMessage,
            file,
            type: 'SET_ERROR',
          });
        }
      }
    },
    [store, onUpload, onProgress]
  );

  const onFilesChange = React.useCallback(
    (originalFiles: File[]) => {
      if (disabled) return;

      let filesToProcess = [...originalFiles];
      let invalid = false;

      if (maxFiles) {
        const currentCount = store.getState().files.size;
        const remainingSlotCount = Math.max(0, maxFiles - currentCount);

        if (remainingSlotCount < filesToProcess.length) {
          const rejectedFiles = filesToProcess.slice(remainingSlotCount);
          invalid = true;

          filesToProcess = filesToProcess.slice(0, remainingSlotCount);

          for (const file of rejectedFiles) {
            let rejectionMessage = `Maximum ${maxFiles} files allowed`;

            if (onFileValidate) {
              const validationMessage = onFileValidate(file);
              if (validationMessage) {
                rejectionMessage = validationMessage;
              }
            }

            onFileReject?.(file, rejectionMessage);
          }
        }
      }

      const acceptedFiles: File[] = [];
      const rejectedFiles: { file: File; message: string }[] = []; //NOSONAR

      for (const file of filesToProcess) {
        let rejected = false;
        let rejectionMessage = '';

        if (onFileValidate) {
          const validationMessage = onFileValidate(file);
          if (validationMessage) {
            rejectionMessage = validationMessage;
            onFileReject?.(file, rejectionMessage);
            invalid = true;
            continue;
          }
        }

        if (acceptTypes) {
          const fileType = file.type;
          const fileExtension = `.${file.name.split('.').pop()}`;

          if (
            !acceptTypes.some(
              (type) =>
                type === fileType ||
                type === fileExtension ||
                (type.includes('/*') && fileType.startsWith(type.replace('/*', '/')))
            )
          ) {
            rejectionMessage = 'File type not accepted';
            onFileReject?.(file, rejectionMessage);
            rejected = true;
            invalid = true;
          }
        }

        if (maxSize && file.size > maxSize) {
          rejectionMessage = 'File too large';
          onFileReject?.(file, rejectionMessage);
          rejected = true;
          invalid = true;
        }

        if (!rejected) {
          acceptedFiles.push(file);
        } else {
          rejectedFiles.push({ file, message: rejectionMessage });
        }
      }

      if (invalid) {
        store.dispatch({ invalid, type: 'SET_INVALID' });
        setTimeout(() => {
          store.dispatch({ invalid: false, type: 'SET_INVALID' });
        }, 2000);
      }

      if (acceptedFiles.length > 0) {
        store.dispatch({ files: acceptedFiles, type: 'ADD_FILES' });

        if (isControlled && onValueChange) {
          const currentFiles = Array.from(store.getState().files.values()).map((f) => f.file);
          onValueChange([...currentFiles]);
        }

        if (onAccept) {
          onAccept(acceptedFiles);
        }

        for (const file of acceptedFiles) {
          onFileAccept?.(file);
        }

        if (onUpload) {
          requestAnimationFrame(() => {
            onFilesUpload(acceptedFiles);
          });
        }
      }
    },
    [
      store,
      isControlled,
      onValueChange,
      onAccept,
      onFileAccept,
      onUpload,
      onFilesUpload,
      maxFiles,
      onFileValidate,
      onFileReject,
      acceptTypes,
      maxSize,
      disabled,
    ]
  );

  const onInputChange = React.useCallback(
    (event: React.ChangeEvent<HTMLInputElement>) => {
      const files = Array.from(event.target.files ?? []);
      onFilesChange(files);
      event.target.value = '';
    },
    [onFilesChange]
  );

  const contextValue = React.useMemo<FileUploadContextValue>(
    () => ({
      dir,
      disabled,
      dropzoneId,
      inputId,
      inputRef,
      labelId,
      listId,
      onFilesChange,
      urlCache,
    }),
    [dropzoneId, inputId, listId, labelId, dir, disabled, urlCache, onFilesChange]
  );

  const RootPrimitive = asChild ? Slot : 'div';

  return (
    <StoreContext.Provider value={store}>
      <FileUploadContext.Provider value={contextValue}>
        <RootPrimitive
          data-disabled={disabled ? '' : undefined}
          data-slot="file-upload"
          dir={dir}
          {...rootProps}
          className={cn('relative flex flex-col gap-2', className)}
        >
          {children}
          <input
            type="file"
            id={inputId}
            aria-labelledby={labelId}
            aria-describedby={dropzoneId}
            ref={inputRef}
            tabIndex={-1}
            accept={accept}
            name={name}
            className="sr-only"
            disabled={disabled}
            multiple={multiple}
            required={required}
            onChange={onInputChange}
          />
          <span id={labelId} className="sr-only">
            {label ?? 'File upload'}
          </span>
        </RootPrimitive>
      </FileUploadContext.Provider>
    </StoreContext.Provider>
  );
};

interface FileUploadDropzoneProps extends React.ComponentProps<'div'> {
  asChild?: boolean;
}

const FileUploadDropzone = (props: FileUploadDropzoneProps): JSX.Element => {
  const {
    asChild,
    className,
    onClick: onClickProp,
    onDragEnter: onDragEnterProp,
    onDragLeave: onDragLeaveProp,
    onDragOver: onDragOverProp,
    onDrop: onDropProp,
    onKeyDown: onKeyDownProp,
    onPaste: onPasteProp,
    ...dropzoneProps
  } = props;

  const context = useFileUploadContext(DROPZONE_NAME);
  const { onFilesChange } = context;
  const store = useStoreContext(DROPZONE_NAME);
  const dragOver = useStore((state) => state.dragOver);
  const invalid = useStore((state) => state.invalid);

  const onClick = React.useCallback(
    (event: React.MouseEvent<HTMLDivElement>) => {
      onClickProp?.(event);

      if (event.defaultPrevented) return;

      const target = event.target;

      const isFromTrigger =
        target instanceof HTMLElement && target.closest('[data-slot="file-upload-trigger"]');

      if (!isFromTrigger) {
        context.inputRef.current?.click();
      }
    },
    [context.inputRef, onClickProp]
  );

  const onDragOver = React.useCallback(
    (event: React.DragEvent<HTMLDivElement>) => {
      onDragOverProp?.(event);

      if (event.defaultPrevented) return;

      event.preventDefault();
      store.dispatch({ dragOver: true, type: 'SET_DRAG_OVER' });
    },
    [store, onDragOverProp]
  );

  const onDragEnter = React.useCallback(
    (event: React.DragEvent<HTMLDivElement>) => {
      onDragEnterProp?.(event);

      if (event.defaultPrevented) return;

      event.preventDefault();
      store.dispatch({ dragOver: true, type: 'SET_DRAG_OVER' });
    },
    [store, onDragEnterProp]
  );

  const onDragLeave = React.useCallback(
    (event: React.DragEvent<HTMLDivElement>) => {
      onDragLeaveProp?.(event);

      if (event.defaultPrevented) return;

      const relatedTarget = event.relatedTarget;
      if (
        relatedTarget &&
        relatedTarget instanceof Node &&
        event.currentTarget.contains(relatedTarget)
      ) {
        return;
      }

      event.preventDefault();
      store.dispatch({ dragOver: false, type: 'SET_DRAG_OVER' });
    },
    [store, onDragLeaveProp]
  );

  const onDrop = React.useCallback(
    (event: React.DragEvent<HTMLDivElement>) => {
      onDropProp?.(event);

      if (event.defaultPrevented) return;

      event.preventDefault();
      store.dispatch({ dragOver: false, type: 'SET_DRAG_OVER' });

      const files = Array.from(event.dataTransfer.files);
      onFilesChange(files);
    },
    [store, onDropProp, onFilesChange]
  );

  const onPaste = React.useCallback(
    (event: React.ClipboardEvent<HTMLDivElement>) => {
      onPasteProp?.(event);

      if (event.defaultPrevented) return;

      event.preventDefault();
      store.dispatch({ dragOver: false, type: 'SET_DRAG_OVER' });

      const items = event.clipboardData?.items;
      if (!items) return;

      const files: File[] = [];
      for (const item of items) {
        if (item?.kind === 'file') {
          const file = item.getAsFile();
          if (file) {
            files.push(file);
          }
        }
      }

      if (files.length === 0) return;

      onFilesChange(files);
    },
    [store, onPasteProp, onFilesChange]
  );

  const onKeyDown = React.useCallback(
    (event: React.KeyboardEvent<HTMLDivElement>) => {
      onKeyDownProp?.(event);

      if (!event.defaultPrevented && (event.key === 'Enter' || event.key === ' ')) {
        event.preventDefault();
        context.inputRef.current?.click();
      }
    },
    [context.inputRef, onKeyDownProp]
  );

  const DropzonePrimitive = asChild ? Slot : 'div';

  return (
    <DropzonePrimitive
      role="aria-label"
      id={context.dropzoneId}
      aria-controls={`${context.inputId} ${context.listId}`}
      data-disabled={context.disabled ? '' : undefined}
      data-dragging={dragOver ? '' : undefined}
      data-invalid={invalid ? '' : undefined}
      data-slot="file-upload-dropzone"
      dir={context.dir}
      tabIndex={context.disabled ? undefined : 0}
      {...dropzoneProps}
      className={cn(
        'relative flex select-none flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed p-6 outline-none transition-colors hover:bg-accent/30 focus-visible:border-ring/50 data-[disabled]:pointer-events-none data-[dragging]:border-primary/30 data-[invalid]:border-destructive data-[dragging]:bg-accent/30 data-[invalid]:ring-destructive/20',
        className
      )}
      onClick={onClick}
      onDragEnter={onDragEnter}
      onDragLeave={onDragLeave}
      onDragOver={onDragOver}
      onDrop={onDrop}
      onKeyDown={onKeyDown}
      onPaste={onPaste}
    />
  );
};

interface FileUploadTriggerProps extends React.ComponentProps<'button'> {
  asChild?: boolean;
}

const FileUploadTrigger = (props: FileUploadTriggerProps): JSX.Element => {
  const { asChild, onClick: onClickProp, ...triggerProps } = props;
  const context = useFileUploadContext(TRIGGER_NAME);

  const onClick = React.useCallback(
    (event: React.MouseEvent<HTMLButtonElement>) => {
      onClickProp?.(event);

      if (event.defaultPrevented) return;

      context.inputRef.current?.click();
    },
    [context.inputRef, onClickProp]
  );

  const TriggerPrimitive = asChild ? Slot : 'button';

  return (
    <TriggerPrimitive
      type="button"
      aria-controls={context.inputId}
      data-disabled={context.disabled ? '' : undefined}
      data-slot="file-upload-trigger"
      {...triggerProps}
      disabled={context.disabled}
      onClick={onClick}
    />
  );
};

interface FileUploadListProps extends React.ComponentProps<'div'> {
  orientation?: 'horizontal' | 'vertical';
  asChild?: boolean;
  forceMount?: boolean;
}

const FileUploadList = (props: FileUploadListProps): JSX.Element | null => {
  const { asChild, className, forceMount, orientation = 'vertical', ...listProps } = props;

  const context = useFileUploadContext(LIST_NAME);
  const fileCount = useStore((state) => state.files.size);
  const shouldRender = forceMount || fileCount > 0;

  if (!shouldRender) return null;

  const ListPrimitive = asChild ? Slot : 'div';

  return (
    <ListPrimitive
      role="ul"
      id={context.listId}
      data-orientation={orientation}
      data-slot="file-upload-list"
      data-state={shouldRender ? 'active' : 'inactive'}
      dir={context.dir}
      {...listProps}
      className={cn(
        'data-[state=inactive]:fade-out-0 data-[state=active]:fade-in-0 data-[state=inactive]:slide-out-to-top-2 data-[state=active]:slide-in-from-top-2 flex flex-col gap-2 data-[state=active]:animate-in data-[state=inactive]:animate-out',
        orientation === 'horizontal' && 'flex-row overflow-x-auto p-1.5',
        className
      )}
    />
  );
};

interface FileUploadItemContextValue {
  id: string;
  fileState: FileState | undefined;
  nameId: string;
  sizeId: string;
  statusId: string;
  messageId: string;
}

const FileUploadItemContext = React.createContext<FileUploadItemContextValue | null>(null);

const useFileUploadItemContext = (consumerName: string): FileUploadItemContextValue => {
  const context = React.useContext(FileUploadItemContext);
  if (!context) {
    throw new Error(`\`${consumerName}\` must be used within \`${ITEM_NAME}\``);
  }
  return context;
};

interface FileUploadItemProps extends React.ComponentProps<'div'> {
  value: File;
  asChild?: boolean;
}

const FileUploadItem = (props: FileUploadItemProps): JSX.Element | null => {
  const { asChild, className, value, ...itemProps } = props;

  const id = React.useId();
  const statusId = `${id}-status`;
  const nameId = `${id}-name`;
  const sizeId = `${id}-size`;
  const messageId = `${id}-message`;

  const context = useFileUploadContext(ITEM_NAME);
  const fileState = useStore((state) => state.files.get(value));
  const fileCount = useStore((state) => state.files.size);
  const fileIndex = useStore((state) => {
    const files = Array.from(state.files.keys());
    return files.indexOf(value) + 1;
  });

  const itemContext = React.useMemo(
    () => ({
      fileState,
      id,
      messageId,
      nameId,
      sizeId,
      statusId,
    }),
    [id, fileState, statusId, nameId, sizeId, messageId]
  );

  if (!fileState) return null;

  let statusText: string;

  if (fileState.error) {
    statusText = `Error: ${fileState.error}`;
  } else {
    switch (fileState.status) {
      case 'uploading':
        statusText = `Uploading: ${fileState.progress}% complete`;
        break;
      case 'success':
        statusText = 'Upload complete';
        break;
      default:
        statusText = 'Ready to upload';
    }
  }

  const ItemPrimitive = asChild ? Slot : 'div';

  return (
    <FileUploadItemContext.Provider value={itemContext}>
      <ItemPrimitive
        role="li"
        id={id}
        aria-setsize={fileCount}
        aria-posinset={fileIndex}
        aria-describedby={`${nameId} ${sizeId} ${statusId} ${fileState.error ? messageId : ''}`}
        aria-labelledby={nameId}
        data-slot="file-upload-item"
        dir={context.dir}
        {...itemProps}
        className={cn('relative flex items-center gap-2.5 rounded-md border p-3', className)}
      >
        {props.children}
        <span id={statusId} className="sr-only">
          {statusText}
        </span>
      </ItemPrimitive>
    </FileUploadItemContext.Provider>
  );
};

const formatBytes = (bytes: number): string => {
  if (bytes === 0) return '0 B';
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
  const i = Math.floor(Math.log(bytes) / Math.log(1024));
  return `${(bytes / 1024 ** i).toFixed(i ? 1 : 0)} ${sizes[i]}`;
};

const getFileIcon = (file: File): JSX.Element => {
  const type = file.type;
  const extension = file.name.split('.').pop()?.toLowerCase() ?? '';

  if (type.startsWith('video/')) {
    return <FileVideoIcon />;
  }

  if (type.startsWith('audio/')) {
    return <FileAudioIcon />;
  }

  if (type.startsWith('text/') || ['txt', 'md', 'rtf', 'pdf'].includes(extension)) {
    return <FileTextIcon />;
  }

  if (
    [
      'html',
      'css',
      'js',
      'jsx',
      'ts',
      'tsx',
      'json',
      'xml',
      'php',
      'py',
      'rb',
      'java',
      'c',
      'cpp',
      'cs',
    ].includes(extension)
  ) {
    return <FileCodeIcon />;
  }

  if (['zip', 'rar', '7z', 'tar', 'gz', 'bz2'].includes(extension)) {
    return <FileArchiveIcon />;
  }

  if (
    ['exe', 'msi', 'app', 'apk', 'deb', 'rpm'].includes(extension) ||
    type.startsWith('application/')
  ) {
    return <FileCogIcon />;
  }

  return <FileIcon />;
};

interface FileUploadItemPreviewProps extends React.ComponentProps<'div'> {
  render?: (file: File, fallback: () => React.ReactNode) => React.ReactNode;
  asChild?: boolean;
}

const FileUploadItemPreview = (props: FileUploadItemPreviewProps): JSX.Element | null => {
  const { asChild, children, className, render, ...previewProps } = props;

  const itemContext = useFileUploadItemContext(ITEM_PREVIEW_NAME);
  const context = useFileUploadContext(ITEM_PREVIEW_NAME);

  const getDefaultRender = React.useCallback(
    (file: File) => {
      if (itemContext.fileState?.file.type.startsWith('image/')) {
        let url = context.urlCache.get(file);
        if (!url) {
          url = URL.createObjectURL(file);
          context.urlCache.set(file, url);
        }

        return (
          // biome-ignore lint/performance/noImgElement: dynamic file URLs from user uploads don't work well with Next.js Image optimization
          <Image fill src={url} alt={file.name} className="size-full object-cover" />
        );
      }

      return getFileIcon(file);
    },
    [itemContext.fileState?.file.type, context.urlCache]
  );

  const onPreviewRender = React.useCallback(
    (file: File) => {
      if (render) {
        return render(file, () => getDefaultRender(file));
      }

      return getDefaultRender(file);
    },
    [render, getDefaultRender]
  );

  if (!itemContext.fileState) return null;

  const ItemPreviewPrimitive = asChild ? Slot : 'div';

  return (
    <ItemPreviewPrimitive
      aria-labelledby={itemContext.nameId}
      data-slot="file-upload-preview"
      {...previewProps}
      className={cn(
        'relative flex size-10 shrink-0 items-center justify-center overflow-hidden rounded border bg-accent/50 [&>svg]:size-10',
        className
      )}
    >
      {onPreviewRender(itemContext.fileState.file)}
      {children}
    </ItemPreviewPrimitive>
  );
};

interface FileUploadItemMetadataProps extends React.ComponentProps<'div'> {
  asChild?: boolean;
  size?: 'default' | 'sm';
}

const FileUploadItemMetadata = (props: FileUploadItemMetadataProps): JSX.Element | null => {
  const { asChild, children, className, size = 'default', ...metadataProps } = props;

  const context = useFileUploadContext(ITEM_METADATA_NAME);
  const itemContext = useFileUploadItemContext(ITEM_METADATA_NAME);

  if (!itemContext.fileState) return null;

  const ItemMetadataPrimitive = asChild ? Slot : 'div';

  return (
    <ItemMetadataPrimitive
      data-slot="file-upload-metadata"
      dir={context.dir}
      {...metadataProps}
      className={cn('flex min-w-0 flex-1 flex-col', className)}
    >
      {children ?? (
        <>
          <span
            id={itemContext.nameId}
            className={cn(
              'truncate font-medium text-sm',
              size === 'sm' && 'font-normal text-[13px] leading-snug'
            )}
          >
            {itemContext.fileState.file.name}
          </span>
          <span
            id={itemContext.sizeId}
            className={cn(
              'truncate text-muted-foreground text-xs',
              size === 'sm' && 'text-[11px] leading-snug'
            )}
          >
            {formatBytes(itemContext.fileState.file.size)}
          </span>
          {itemContext.fileState.error && (
            <span id={itemContext.messageId} className="text-destructive text-xs">
              {itemContext.fileState.error}
            </span>
          )}
        </>
      )}
    </ItemMetadataPrimitive>
  );
};
interface FileUploadItemProgressProps extends React.ComponentProps<'div'> {
  variant?: 'linear' | 'circular' | 'fill';
  size?: number;
  asChild?: boolean;
  forceMount?: boolean;
}

const FileUploadItemProgress = (props: FileUploadItemProgressProps): JSX.Element | null => {
  const { asChild, className, forceMount, size = 40, variant = 'linear', ...progressProps } = props;

  const itemContext = useFileUploadItemContext(ITEM_PROGRESS_NAME);

  if (!itemContext.fileState) return null;

  const shouldRender = forceMount || itemContext.fileState.progress !== 100;

  if (!shouldRender) return null;

  const ItemProgressPrimitive = asChild ? Slot : 'div';

  switch (variant) {
    case 'circular': {
      const circumference = 2 * Math.PI * ((size - 4) / 2);
      const strokeDashoffset =
        circumference - (itemContext.fileState.progress / 100) * circumference;

      return (
        <ItemProgressPrimitive
          role="progress"
          aria-valuemin={0}
          aria-valuemax={100}
          aria-valuenow={itemContext.fileState.progress}
          aria-valuetext={`${itemContext.fileState.progress}%`}
          aria-labelledby={itemContext.nameId}
          data-slot="file-upload-progress"
          {...progressProps}
          className={cn('-translate-x-1/2 -translate-y-1/2 absolute top-1/2 left-1/2', className)}
        >
          <svg
            className="rotate-[-90deg] transform"
            width={size}
            height={size}
            viewBox={`0 0 ${size} ${size}`}
            fill="none"
            stroke="currentColor"
          >
            <circle
              className="text-primary/20"
              strokeWidth="2"
              cx={size / 2}
              cy={size / 2}
              r={(size - 4) / 2}
            />
            <circle
              className="text-primary transition-[stroke-dashoffset] duration-300 ease-linear"
              strokeWidth="2"
              strokeLinecap="round"
              strokeDasharray={circumference}
              strokeDashoffset={strokeDashoffset}
              cx={size / 2}
              cy={size / 2}
              r={(size - 4) / 2}
            />
          </svg>
        </ItemProgressPrimitive>
      );
    }

    case 'fill': {
      const progressPercentage = itemContext.fileState.progress;
      const topInset = 100 - progressPercentage;

      return (
        <ItemProgressPrimitive
          role="progress"
          aria-valuemin={0}
          aria-valuemax={100}
          aria-valuenow={progressPercentage}
          aria-valuetext={`${progressPercentage}%`}
          aria-labelledby={itemContext.nameId}
          data-slot="file-upload-progress"
          {...progressProps}
          className={cn(
            'absolute inset-0 bg-primary/50 transition-[clip-path] duration-300 ease-linear',
            className
          )}
          style={{
            clipPath: `inset(${topInset}% 0% 0% 0%)`,
          }}
        />
      );
    }

    default:
      return (
        <ItemProgressPrimitive
          role="progress"
          aria-valuemin={0}
          aria-valuemax={100}
          aria-valuenow={itemContext.fileState.progress}
          aria-valuetext={`${itemContext.fileState.progress}%`}
          aria-labelledby={itemContext.nameId}
          data-slot="file-upload-progress"
          {...progressProps}
          className={cn(
            'relative h-1.5 w-full overflow-hidden rounded-full bg-primary/20',
            className
          )}
        >
          <div
            className="h-full w-full flex-1 bg-primary transition-transform duration-300 ease-linear"
            style={{
              transform: `translateX(-${100 - itemContext.fileState.progress}%)`,
            }}
          />
        </ItemProgressPrimitive>
      );
  }
};

interface FileUploadItemDeleteProps extends React.ComponentProps<'button'> {
  asChild?: boolean;
}

const FileUploadItemDelete = (props: FileUploadItemDeleteProps): JSX.Element | null => {
  const { asChild, onClick: onClickProp, ...deleteProps } = props;

  const store = useStoreContext(ITEM_DELETE_NAME);
  const itemContext = useFileUploadItemContext(ITEM_DELETE_NAME);

  const onClick = React.useCallback(
    (event: React.MouseEvent<HTMLButtonElement>) => {
      onClickProp?.(event);

      if (!itemContext.fileState || event.defaultPrevented) return;

      store.dispatch({
        file: itemContext.fileState.file,
        type: 'REMOVE_FILE',
      });
    },
    [store, itemContext.fileState, onClickProp]
  );

  if (!itemContext.fileState) return null;

  const ItemDeletePrimitive = asChild ? Slot : 'button';

  return (
    <ItemDeletePrimitive
      type="button"
      aria-controls={itemContext.id}
      aria-describedby={itemContext.nameId}
      data-slot="file-upload-item-delete"
      {...deleteProps}
      onClick={onClick}
    />
  );
};

interface FileUploadClearProps extends React.ComponentProps<'button'> {
  forceMount?: boolean;
  asChild?: boolean;
}

const FileUploadClear = (props: FileUploadClearProps): JSX.Element | null => {
  const { asChild, disabled, forceMount, onClick: onClickProp, ...clearProps } = props;

  const context = useFileUploadContext(CLEAR_NAME);
  const store = useStoreContext(CLEAR_NAME);
  const fileCount = useStore((state) => state.files.size);

  const isDisabled = disabled || context.disabled;

  const onClick = React.useCallback(
    (event: React.MouseEvent<HTMLButtonElement>) => {
      onClickProp?.(event);

      if (event.defaultPrevented) return;

      store.dispatch({ type: 'CLEAR' });
    },
    [store, onClickProp]
  );

  const shouldRender = forceMount || fileCount > 0;

  if (!shouldRender) return null;

  const ClearPrimitive = asChild ? Slot : 'button';

  return (
    <ClearPrimitive
      type="button"
      aria-controls={context.listId}
      data-slot="file-upload-clear"
      data-disabled={isDisabled ? '' : undefined}
      {...clearProps}
      disabled={isDisabled}
      onClick={onClick}
    />
  );
};

export {
  FileUploadClear as Clear,
  FileUploadDropzone as Dropzone,
  FileUploadRoot as FileUpload,
  FileUploadClear,
  FileUploadDropzone,
  FileUploadItem,
  FileUploadItemDelete,
  FileUploadItemMetadata,
  FileUploadItemPreview,
  FileUploadItemProgress,
  FileUploadList,
  //
  type FileUploadRootProps as FileUploadProps,
  FileUploadTrigger,
  getFileIcon,
  FileUploadItem as Item,
  FileUploadItemDelete as ItemDelete,
  FileUploadItemMetadata as ItemMetadata,
  FileUploadItemPreview as ItemPreview,
  FileUploadItemProgress as ItemProgress,
  FileUploadList as List,
  //
  FileUploadRoot as Root,
  FileUploadTrigger as Trigger,
  //
  useStore as useFileUpload,
};
