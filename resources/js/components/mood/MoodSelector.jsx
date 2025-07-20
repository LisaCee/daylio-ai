import React from 'react';
import './styles.css';

// Export moods array for use in other components
export const moods = [
    { id: 0, name: 'bad', color: 'accent-gray', icon: 'fa-regular fa-face-tired' },
    { id: 1, name: 'poor', color: 'accent-blue', icon: 'fa-regular fa-face-frown' },
    { id: 2, name: 'meh', color: 'accent-purple', icon: 'fa-regular fa-face-meh' },
    { id: 3, name: 'good', color: 'accent-green', icon: 'fa-regular fa-face-smile' },
    { id: 4, name: 'great', color: 'accent-orange', icon: 'fa-regular fa-face-laugh-beam' }
];

// Export getColor function for use in other components
export const getColor = (moodLevel) => {
    const mood = moods.find(m => m.id === moodLevel);
    return mood ? mood.color : 'accent-gray';
};

export default function MoodSelector({ selectedMood, setSelectedMood }) {
    return (
        <fieldset className="mood-section">
            <legend className="section-legend">How are you feeling?</legend>
            <div className="mood-selector">
                {moods.map(mood => (
                    <div key={mood.id} className="mood-option">
                        <input
                            type="radio"
                            id={`mood-${mood.id}`}
                            name="mood"
                            value={mood.id}
                            checked={selectedMood === mood.id}
                            onChange={() => setSelectedMood(mood.id)}
                            className="mood-input"
                        />
                        <label 
                            htmlFor={`mood-${mood.id}`}
                            className={`mood-label ${selectedMood === mood.id ? 'selected' : ''}`}
                        >
                            <i className={`mood-icon ${mood.icon}`}></i>
                        </label>
                    </div>
                ))}
            </div>
        </fieldset>
    );
} 