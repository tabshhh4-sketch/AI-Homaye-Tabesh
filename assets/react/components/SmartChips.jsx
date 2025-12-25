import React from 'react';

/**
 * SmartChips Component
 * Displays suggested action buttons based on user persona
 */
const SmartChips = ({ persona, onChipClick }) => {
    const getChipsForPersona = () => {
        if (!persona) {
            return [
                { id: 'intro', label: 'راهنمایی کن', message: 'می‌خواهم کتابم را چاپ کنم، راهنمایی کن' },
                { id: 'price', label: 'قیمت چاپ', message: 'هزینه چاپ کتاب چقدر است؟' },
                { id: 'quality', label: 'کیفیت چاپ', message: 'انواع کاغذ و کیفیت چاپ چیست؟' }
            ];
        }

        const chips = {
            'نویسنده': [
                { id: 'license', label: 'نیاز به مجوز دارم', message: 'برای چاپ کتابم نیاز به مجوز دارم' },
                { id: 'copyright', label: 'شابک و حق نشر', message: 'در مورد شابک و حق نشر توضیح بده' },
                { id: 'print_type', label: 'نوع چاپ مناسب', message: 'چه نوع چاپی برای کتاب من مناسب است?' }
            ],
            'ناشر': [
                { id: 'bulk', label: 'چاپ انبوه', message: 'می‌خواهم تیراژ بالا چاپ کنم' },
                { id: 'discount', label: 'تخفیف حجمی', message: 'برای چاپ انبوه تخفیف دارید؟' },
                { id: 'quality', label: 'کیفیت ویژه', message: 'بالاترین کیفیت چاپ را می‌خواهم' }
            ],
            'دانشجو': [
                { id: 'thesis', label: 'چاپ پایان‌نامه', message: 'می‌خواهم پایان‌نامه چاپ کنم' },
                { id: 'fast', label: 'چاپ فوری', message: 'به سرعت نیاز دارم' },
                { id: 'budget', label: 'قیمت مناسب', message: 'دنبال قیمت مناسب هستم' }
            ],
            'مدیر': [
                { id: 'catalog', label: 'چاپ کاتالوگ', message: 'می‌خواهم کاتالوگ محصولات چاپ کنم' },
                { id: 'brochure', label: 'بروشور شرکتی', message: 'نیاز به چاپ بروشور دارم' },
                { id: 'invoice', label: 'چاپ اوراق اداری', message: 'فاکتور و سربرگ می‌خواهم' }
            ]
        };

        return chips[persona] || chips['نویسنده'];
    };

    const chips = getChipsForPersona();

    return (
        <div className="homa-smart-chips">
            {chips.map((chip) => (
                <button
                    key={chip.id}
                    className="homa-chip"
                    onClick={() => onChipClick(chip)}
                >
                    {chip.label}
                </button>
            ))}
        </div>
    );
};

export default SmartChips;
