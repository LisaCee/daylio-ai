import React from 'react';
import './styles.css';

export const activities = [
    { id: 1, name: 'Work', icon: 'fa-solid fa-briefcase' },
    { id: 2, name: 'Exercise', icon: 'fa-solid fa-dumbbell' },
    { id: 3, name: 'Social', icon: 'fa-solid fa-users' },
    { id: 4, name: 'Hobby', icon: 'fa-solid fa-palette' },
    { id: 5, name: 'Food', icon: 'fa-solid fa-utensils' },
    { id: 6, name: 'Sleep', icon: 'fa-solid fa-bed' },
    { id: 7, name: 'Reading', icon: 'fa-solid fa-book' },
    { id: 8, name: 'Music', icon: 'fa-solid fa-music' }
];

export default function ActivitySelector({ selectedActivities, onActivityToggle }) {
    return (
        <div className="activities-section">
            <h3>Activities</h3>
            <div className="activities-grid">
                {activities.map(activity => (
                    <label
                        key={activity.id}
                        className={`activity-label ${selectedActivities.includes(activity.id) ? 'selected' : ''}`}
                    >
                        <input
                            type="checkbox"
                            checked={selectedActivities.includes(activity.id)}
                            onChange={() => onActivityToggle(activity.id)}
                            style={{ display: 'none' }}
                        />
                        <span className="activity-icon">
                            <i className={activity.icon}></i>
                        </span>
                        <span className="activity-name">{activity.name}</span>
                    </label>
                ))}
            </div>
        </div>
    );
} 