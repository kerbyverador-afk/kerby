

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const updatePaymentCountdowns = () => {
    document.querySelectorAll('[data-due-countdown]').forEach((element) => {
        const [year, month, day] = element.dataset.dueDate.split('-').map(Number);
        const dueDate = new Date(year, month - 1, day);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const days = Math.round((dueDate - today) / 86400000);

        element.hidden = days > 5;
        element.classList.toggle('is-overdue', days < 0);
        element.classList.toggle('is-reminder', days >= 0);
        element.textContent = days < 0
            ? `Overdue by ${Math.abs(days)} ${Math.abs(days) === 1 ? 'day' : 'days'}`
            : days === 0
                ? 'Due today'
                : `Due in ${days} ${days === 1 ? 'day' : 'days'}`;
    });
};

updatePaymentCountdowns();
window.setInterval(updatePaymentCountdowns, 60000);

document.querySelectorAll('[data-payment-coverage]').forEach((form) => {
    const monthYear = form.querySelector('[name="month_year"]');
    const totalAmount = form.querySelector('[data-amount-due]');
    const amountPaid = form.querySelector('[data-amount-paid]');
    const amountDueHelp = form.querySelector('[data-amount-due-help]');
    const amountHelp = form.querySelector('[data-amount-paid-help]');
    const fullPayment = form.querySelector('input[name="payment_coverage"][value="full"]');
    const partialPayment = form.querySelector('input[name="payment_coverage"][value="partial"]');
    const submitButton = form.querySelector('[data-payment-submit]');
    const dueAmountsByMonth = JSON.parse(form.dataset.dueAmounts || '{}');
    const pendingMonthsByMonth = JSON.parse(form.dataset.pendingMonths || '{}');

    if (!monthYear || !totalAmount || !amountPaid || !amountDueHelp || !amountHelp || !fullPayment || !partialPayment) {
        return;
    }

    const updateAmountField = (clearAutomaticallyFilledAmount = false) => {
        if (fullPayment.checked) {
            amountPaid.value = totalAmount.value;
            amountPaid.readOnly = true;
            amountPaid.required = false;
            amountPaid.removeAttribute('max');
            amountHelp.textContent = totalAmount.value
                ? 'Automatically filled with the amount due for this month.'
                : 'No admin-assigned amount is available for this month.';

            return;
        }

        if (clearAutomaticallyFilledAmount && amountPaid.readOnly && amountPaid.value === totalAmount.value) {
            amountPaid.value = '';
        }

        amountPaid.readOnly = false;
        amountPaid.required = true;
        const total = Number(totalAmount.value);
        if (Number.isFinite(total) && total > 0) {
            amountPaid.max = Math.max(0, total - 0.01).toFixed(2);
        } else {
            amountPaid.removeAttribute('max');
        }
        amountHelp.textContent = 'Enter the amount you are paying. It must be less than the total due.';
    };

    const updateAdminAssignedDue = (clearAutomaticallyFilledAmount = false) => {
        const dueAmount = dueAmountsByMonth[monthYear.value];
        totalAmount.value = dueAmount ?? '';
        amountDueHelp.textContent = dueAmount
            ? 'Automatically filled from your admin-assigned outstanding payment.'
            : 'No outstanding payment was set by the admin for this month. Please contact the admin.';
        updateAmountField(clearAutomaticallyFilledAmount);

        if (pendingMonthsByMonth[monthYear.value]) {
            amountDueHelp.textContent = 'Your payment for this month is awaiting admin confirmation.';
            amountPaid.value = '';
            amountPaid.readOnly = true;
            amountPaid.required = false;
            amountPaid.removeAttribute('max');
            amountHelp.textContent = 'A second submission is unavailable until the admin confirms or rejects the pending payment.';
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Awaiting admin confirmation';
            }
        } else if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = 'Submit payment';
        }
    };

    monthYear.addEventListener('change', () => updateAdminAssignedDue());
    fullPayment.addEventListener('change', () => updateAdminAssignedDue());
    partialPayment.addEventListener('change', () => updateAdminAssignedDue(true));
    updateAdminAssignedDue();
});
