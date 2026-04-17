-- Demo data for testing the AI Chatbot Builder
-- Run this AFTER schema.sql

USE ai_chatbot_builder;

-- Demo business (login: demo@example.com / password: demo123)
INSERT INTO businesses (name, email, password_hash, website_url, status) VALUES
('Demo Company', 'demo@example.com', '$2y$10$vyShP3pXMHEUXh0O8XfQDe/8zjc72iQCdc8ZRpVidCEmcV1JhUGWW', 'https://example.com', 'active');

SET @business_id = LAST_INSERT_ID();

-- Demo chatbot
INSERT INTO chatbot_bots (business_id, name, welcome_message, theme_color, position, is_active) VALUES
(@business_id, 'Support Bot', 'Hi! I''m here to help. Ask me about our products, shipping, or returns.', '#2563eb', 'bottom-right', 1);

SET @bot_id = LAST_INSERT_ID();

-- Demo FAQs
INSERT INTO chatbot_faqs (bot_id, question, answer, sort_order) VALUES
(@bot_id, 'What are your business hours?', 'We are open Monday to Friday, 9am to 6pm EST. On weekends we have limited support via email.', 1),
(@bot_id, 'How can I track my order?', 'You can track your order by logging into your account and clicking "Track Order" in your order history. You will also receive a tracking number via email once your order ships.', 2),
(@bot_id, 'What is your return policy?', 'We offer a 30-day return policy. Items must be unused and in original packaging. Contact our support team to initiate a return.', 3),
(@bot_id, 'Do you offer free shipping?', 'Yes! Free shipping on all orders over $50. Standard shipping is $5.99 for orders under $50.', 4),
(@bot_id, 'How do I contact support?', 'You can reach us by email at support@example.com or by phone at 1-800-123-4567 during business hours.', 5);

-- Demo document (text content for knowledge base)
INSERT INTO chatbot_documents (bot_id, filename, file_path, file_type, content_text, processed_at) VALUES
(@bot_id, 'company_info.txt', 'demo_company_info.txt', 'txt', 
'Our Company - About Us

We are a leading provider of quality products since 2010. Our mission is to deliver exceptional value to our customers.

Products:
- Electronics: Phones, tablets, laptops
- Home & Garden: Furniture, decor items
- Clothing: Men, women, kids apparel

Shipping:
- Standard: 3-5 business days
- Express: 1-2 business days
- Free shipping on orders over $50

Payment Methods:
- Credit/Debit cards
- PayPal
- Bank transfer

We are based in New York, USA. Our warehouse is open for pickups by appointment only.',
NOW());
