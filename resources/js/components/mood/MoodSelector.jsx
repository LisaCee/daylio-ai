import React from 'react';
import './styles.css';

export const moods = [
    { id: 4, name: 'Great', icon: 'fa-regular fa-face-laugh-beam' },
    { id: 3, name: 'Good', icon: 'fa-regular fa-face-smile' },
    { id: 2, name: 'Meh', icon: 'fa-regular fa-face-meh' },
    { id: 1, name: 'Poor', icon: 'fa-regular fa-face-frown' },
    { id: 0, name: 'Bad', icon: 'fa-regular fa-face-tired' }
];

export default function MoodSelector({ selectedMood, onMoodSelect }) {
    return (
        <fieldset className="moods-section">
            <legend className="moods-legend">
                <h2>How are you feeling?</h2>
            </legend>
            <div className="moods-grid">
                {moods.map(mood => (
                    <label
                        key={mood.id}
                        className={`mood-label ${selectedMood === mood.id ? 'selected' : ''}`}
                        data-mood={mood.id}
                    >
                        <input
                            type="radio"
                            name="mood"
                            value={mood.id}
                            checked={selectedMood === mood.id}
                            onChange={() => onMoodSelect(mood.id)}
                            style={{ display: 'none' }}
                        />
                        <span className="mood-icon">
                            <i className={mood.icon}></i>
                        </span>
                        <span className="mood-name">{mood.name}</span>
                    </label>
                ))}
            </div>
        </fieldset>
    );
} 