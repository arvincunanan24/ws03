<?= loadPartial('head') ?>
<?= loadPartial('navbar') ?>
<?= loadPartial('top-banner') ?>

<section class="min-h-[50vh] flex items-center justify-center bg-[#F5F5F5]">
    <div class="container mx-auto p-4 mt-4">
        <div class="text-center text-3xl mb-4 font-bold border-t-4 border-t-[#FFC107] border-x border-b border-gray-300 p-8 bg-white shadow-md rounded-lg">
            <span class="block text-6xl mb-2">⚠️</span>
            404 Error
        </div>

        <p class="text-center text-2xl mb-8 text-[#757575] font-medium italic">
            This page does not exist
        </p>

        <div class="flex justify-center">
            <a href="/" class="bg-[#FFC107] text-[#212121] px-10 py-3 rounded-full font-bold shadow-sm hover:shadow-md hover:bg-white border border-[#FFC107] transition duration-300 uppercase text-xs tracking-widest">
                Return to Dashboard
            </a>
        </div>
    </div>
</section>

<?= loadPartial('bottom-banner'); ?>