ALTER TABLE invoices
    ADD COLUMN checkout_token CHAR(64) NULL AFTER invoice_no,
    ADD UNIQUE KEY uq_invoices_checkout_token (checkout_token);
