-- CNA Upholstery Management System Database Schema

-- Create database (run this as superuser)
-- CREATE DATABASE cna_upholstery WITH ENCODING 'UTF8' LC_COLLATE='en_US.UTF-8' LC_CTYPE='en_US.UTF-8';

-- Extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Users table for authentication
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('admin', 'user')),
    is_active BOOLEAN DEFAULT true,
    email_verified_at TIMESTAMP,
    last_login TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Clients table
CREATE TABLE clients (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    company VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(50),
    zip_code VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Estimates table
CREATE TABLE estimates (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    estimate_number VARCHAR(50) UNIQUE NOT NULL,
    client_id UUID NOT NULL REFERENCES clients(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES users(id),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    subtotal DECIMAL(10, 2) NOT NULL DEFAULT 0,
    tax_rate DECIMAL(5, 4) NOT NULL DEFAULT 6.6250,
    tax_amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    total DECIMAL(10, 2) NOT NULL DEFAULT 0,
    status VARCHAR(20) DEFAULT 'draft' CHECK (status IN ('draft', 'sent', 'approved', 'rejected', 'expired')),
    valid_until DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Estimate items table
CREATE TABLE estimate_items (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    estimate_id UUID NOT NULL REFERENCES estimates(id) ON DELETE CASCADE,
    description TEXT NOT NULL,
    quantity DECIMAL(8, 2) NOT NULL DEFAULT 1,
    unit_price DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Invoices table
CREATE TABLE invoices (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    estimate_id UUID REFERENCES estimates(id),
    client_id UUID NOT NULL REFERENCES clients(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES users(id),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    subtotal DECIMAL(10, 2) NOT NULL DEFAULT 0,
    tax_rate DECIMAL(5, 4) NOT NULL DEFAULT 6.6250,
    tax_amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    total DECIMAL(10, 2) NOT NULL DEFAULT 0,
    amount_paid DECIMAL(10, 2) NOT NULL DEFAULT 0,
    status VARCHAR(20) DEFAULT 'draft' CHECK (status IN ('draft', 'sent', 'partial', 'paid', 'overdue', 'cancelled')),
    due_date DATE,
    paid_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Invoice items table
CREATE TABLE invoice_items (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    invoice_id UUID NOT NULL REFERENCES invoices(id) ON DELETE CASCADE,
    description TEXT NOT NULL,
    quantity DECIMAL(8, 2) NOT NULL DEFAULT 1,
    unit_price DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Portfolio items table
CREATE TABLE portfolio_items (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    before_image_url TEXT,
    after_image_url TEXT,
    client_id UUID REFERENCES clients(id),
    is_featured BOOLEAN DEFAULT false,
    is_public BOOLEAN DEFAULT true,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Security logs table
CREATE TABLE security_logs (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES users(id),
    ip_address INET,
    user_agent TEXT,
    event_type VARCHAR(50) NOT NULL,
    event_data JSONB,
    success BOOLEAN,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- System settings table
CREATE TABLE settings (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    key VARCHAR(100) UNIQUE NOT NULL,
    value TEXT,
    description TEXT,
    type VARCHAR(20) DEFAULT 'string' CHECK (type IN ('string', 'number', 'boolean', 'json')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for performance
CREATE INDEX idx_clients_email ON clients(email);
CREATE INDEX idx_clients_phone ON clients(phone);
CREATE INDEX idx_clients_company ON clients(company);

CREATE INDEX idx_estimates_client_id ON estimates(client_id);
CREATE INDEX idx_estimates_user_id ON estimates(user_id);
CREATE INDEX idx_estimates_status ON estimates(status);
CREATE INDEX idx_estimates_number ON estimates(estimate_number);

CREATE INDEX idx_invoices_client_id ON invoices(client_id);
CREATE INDEX idx_invoices_user_id ON invoices(user_id);
CREATE INDEX idx_invoices_status ON invoices(status);
CREATE INDEX idx_invoices_number ON invoices(invoice_number);

CREATE INDEX idx_portfolio_category ON portfolio_items(category);
CREATE INDEX idx_portfolio_featured ON portfolio_items(is_featured);
CREATE INDEX idx_portfolio_public ON portfolio_items(is_public);

CREATE INDEX idx_security_logs_user_id ON security_logs(user_id);
CREATE INDEX idx_security_logs_event_type ON security_logs(event_type);
CREATE INDEX idx_security_logs_created_at ON security_logs(created_at);

-- Functions for updated_at timestamps
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Triggers for updated_at
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_clients_updated_at BEFORE UPDATE ON clients
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_estimates_updated_at BEFORE UPDATE ON estimates
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_invoices_updated_at BEFORE UPDATE ON invoices
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_portfolio_items_updated_at BEFORE UPDATE ON portfolio_items
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_settings_updated_at BEFORE UPDATE ON settings
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Insert default settings
INSERT INTO settings (key, value, description, type) VALUES
('business_name', 'CNA Upholstery', 'Business name for branding', 'string'),
('business_address', '', 'Business address for documents', 'string'),
('business_phone', '', 'Business phone number', 'string'),
('business_email', '', 'Business email address', 'string'),
('tax_rate', '6.6250', 'Default tax rate percentage', 'number'),
('default_language', 'en', 'Default system language', 'string'),
('estimate_validity_days', '30', 'Default estimate validity in days', 'number'),
('invoice_due_days', '30', 'Default invoice due date in days', 'number'),
('aws_s3_bucket', '', 'AWS S3 bucket name for file storage', 'string'),
('max_upload_size', '10485760', 'Maximum file upload size in bytes', 'number');

-- Insert default admin user (password: admin123 - change in production!)
INSERT INTO users (username, email, password_hash, first_name, last_name, role, email_verified_at)
VALUES (
    'admin',
    'admin@cnaupholstery.com',
    '$argon2id$v=19$m=65536,t=4,p=3$c2FsdGVkX19YbWR1ZHRlQ0lVQUZDU1BFUlNFQ1VSSVRZUQ$ZX1234567890abcdef',
    'Administrator',
    'User',
    'admin',
    CURRENT_TIMESTAMP
);