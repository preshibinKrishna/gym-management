import Alpine from 'alpinejs';
import trainingVideo from './training-video';

window.Alpine = Alpine;
Alpine.data('trainingVideo', trainingVideo);

window.photoUploader = () => ({
    fileName: '',
    fileSize: '',
    processing: false,
    async compress(event) {
        const input = event.target;
        const file = input.files?.[0];

        if (!file || !file.type.startsWith('image/')) {
            return;
        }

        this.processing = true;
        try {
            const image = await createImageBitmap(file);
            let maxDimension = 1600;
            let blob = await this.toJpeg(image, maxDimension, 0.92);

            while (blob.size > 200 * 1024 && maxDimension > 480) {
                maxDimension = Math.round(maxDimension * 0.85);
                blob = await this.toJpeg(image, maxDimension, 0.88);
            }

            let quality = 0.84;
            while (blob.size > 200 * 1024 && quality >= 0.48) {
                blob = await this.toJpeg(image, maxDimension, quality);
                quality -= 0.05;
            }

            const optimized = new File([blob], `${file.name.replace(/\.[^.]+$/, '')}.jpg`, { type: 'image/jpeg', lastModified: Date.now() });
            const transfer = new DataTransfer();
            transfer.items.add(optimized);
            input.files = transfer.files;
            this.fileName = optimized.name;
            this.fileSize = `${Math.round(optimized.size / 1024)} KB`;
        } finally {
            this.processing = false;
        }
    },
    toJpeg(image, maxDimension, quality) {
        const scale = Math.min(1, maxDimension / Math.max(image.width, image.height));
        const canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(image.width * scale));
        canvas.height = Math.max(1, Math.round(image.height * scale));
        canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height);

        return new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality));
    },
});

Alpine.start();
