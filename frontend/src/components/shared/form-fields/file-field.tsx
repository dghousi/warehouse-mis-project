'use client';

import { JSX } from 'react';
import Image from 'next/image';
import { Button } from '@/components/ui/button';
import {
  FileUpload,
  FileUploadDropzone,
  FileUploadItem,
  FileUploadItemDelete,
  FileUploadItemMetadata,
  FileUploadItemPreview,
  FileUploadList,
  FileUploadTrigger,
  getFileIcon,
} from '@/components/ui/file-upload';
import { Upload, X } from 'lucide-react';
import { Controller, FieldValues, RegisterOptions, useFormContext } from 'react-hook-form';
import { toast } from 'sonner';

type ValidationOptions = Omit<
  RegisterOptions<FieldValues, string>,
  'disabled' | 'setValueAs' | 'valueAsNumber' | 'valueAsDate'
> & {
  maxSize?: { message: string; value: number };
  mimes?: { message: string; value: string[] };
};

const getFileNamePreview = (file: File): string =>
  file.name.length > 20 ? `${file.name.slice(0, 20)}...` : file.name;

const handleFileReject = (file: File, message: string): void => {
  toast(message, {
    description: `"${getFileNamePreview(file)}" has been rejected`,
  });
};

const mimeTypeMap: Record<string, string> = {
  doc: 'application/msword',
  docx: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  jpeg: 'image/jpeg',
  jpg: 'image/jpeg',
  pdf: 'application/pdf',
  png: 'image/png',
  xls: 'application/vnd.ms-excel',
  xlsx: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
};

type Props = {
  name: string;
  validation?: ValidationOptions;
};

const createMockFileFromPath = (path: string): File => {
  const fileName = path.split('/').pop() || 'file';
  const extension = fileName.split('.').pop()?.toLowerCase() || '';
  const mimeType =
    mimeTypeMap[extension] ||
    (['profilePhotoPath'].includes(path.split('/').pop() || '')
      ? 'image/jpeg'
      : 'application/octet-stream');
  return new File([], fileName, { type: mimeType });
};

const getAcceptValue = (isProfilePhoto: boolean, mimes?: string[]): string => {
  if (mimes?.length) {
    return mimes.map((ext) => `.${ext}`).join(',');
  }
  return isProfilePhoto ? 'image/*' : '.pdf,.doc,.docx,.xls,.xlsx';
};

export const FileField = ({ name, validation }: Props): JSX.Element => {
  const { control } = useFormContext();
  const isProfilePhoto = name === 'profilePhotoPath';

  const accept: string = getAcceptValue(isProfilePhoto, validation?.mimes?.value);

  const maxSize = (validation?.maxSize?.value ?? 5120) * 1024;

  return (
    <Controller
      name={name}
      control={control}
      rules={validation}
      render={({ field }) => {
        const isExistingFile = typeof field.value === 'string' && field.value;

        const fileValue = isExistingFile
          ? createMockFileFromPath(field.value as string)
          : field.value;

        return (
          <FileUpload
            maxFiles={1}
            maxSize={maxSize}
            value={fileValue ? [fileValue] : []}
            onValueChange={(files) => {
              field.onChange(files[0] ?? null);
            }}
            onFileReject={handleFileReject}
            multiple={false}
            accept={accept}
          >
            <FileUploadDropzone>
              <div className="flex flex-col items-center gap-1 text-center">
                <div className="flex items-center justify-center rounded-full border p-2.5">
                  <Upload className="size-6 text-muted-foreground" />
                </div>
                <p className="font-medium text-sm">Drag & drop file here</p>
                <p className="text-muted-foreground text-xs">
                  Or click to browse (max 1 file, up to {maxSize / 1024 / 1024}MB)
                </p>
              </div>
              <FileUploadTrigger asChild id={name}>
                <Button variant="outline" size="sm" className="mt-2 w-fit">
                  Browse files
                </Button>
              </FileUploadTrigger>
            </FileUploadDropzone>

            <FileUploadList>
              {fileValue && (
                <FileUploadItem key={fileValue.name || fileValue} value={fileValue}>
                  <FileUploadItemPreview
                    render={(file, fallback) => {
                      if (isExistingFile) {
                        if (isProfilePhoto) {
                          return (
                            <Image
                              src={field.value as string}
                              alt={file.name}
                              width={44}
                              height={44}
                              className="size-full object-cover"
                              onError={() => toast.error('Failed to load profile image')}
                            />
                          );
                        }
                        return getFileIcon(file);
                      }
                      return fallback();
                    }}
                  />
                  <FileUploadItemMetadata />
                  <FileUploadItemDelete asChild>
                    <Button
                      variant="ghost"
                      size="icon"
                      onClick={() => {
                        field.onChange(null);
                      }}
                    >
                      <X />
                    </Button>
                  </FileUploadItemDelete>
                </FileUploadItem>
              )}
            </FileUploadList>
          </FileUpload>
        );
      }}
    />
  );
};
