import React from 'react';
import './styles.css';

export default function Navigation({ showForm, setShowForm }) {
    return (
        <nav className="nav">
            <div className="nav-content">
                <a href="/" className="nav-link">Home</a>
                <div className="nav-buttons">
                    <button 
                        className="btn-primary"
                        onClick={() => setShowForm(true)}
                    >
                        Add Entry
                    </button>
                </div>
            </div>
        </nav>
    );
} 