import React, { useState } from 'react';
import './App.css';
import MoodSelector, { getColor, moods } from './mood/MoodSelector';
import Navigation from './navigation/Navigation';

export default function App() {
    const [entries, setEntries] = useState([]);
    const [showForm, setShowForm] = useState(false);
    const [selectedMood, setSelectedMood] = useState(null);
    const [selectedActivities, setSelectedActivities] = useState([]);

    const activities = [
        { id: 1, name: 'Work', icon: '💼' },
        { id: 2, name: 'Exercise', icon: '🏃‍♂️' },
        { id: 3, name: 'Social', icon: '👥' },
        { id: 4, name: 'Hobby', icon: '🎨' },
        { id: 5, name: 'Food', icon: '🍕' },
        { id: 6, name: 'Sleep', icon: '😴' },
        { id: 7, name: 'Reading', icon: '📚' },
        { id: 8, name: 'Music', icon: '🎵' }
    ];

    const handleSubmit = (e) => {
        e.preventDefault();
        if (selectedMood === null) return;

        const newEntry = {
            id: Date.now(),
            mood_level: selectedMood,
            activities: selectedActivities,
            created_at: new Date().toISOString()
        };

        setEntries(prev => [newEntry, ...prev]);
        setSelectedMood(null);
        setSelectedActivities([]);
        setShowForm(false);
    };

    const toggleActivity = (activityId) => {
        setSelectedActivities(prev => 
            prev.includes(activityId) 
                ? prev.filter(id => id !== activityId)
                : [...prev, activityId]
        );
    };

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return {
            day: date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }).toUpperCase(),
            time: date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' })
        };
    };

    return (
        <div className="app">
            <Navigation showForm={showForm} setShowForm={setShowForm} />

            <main className="main">
                {showForm ? (
                    <div className="form-container">
                        <h1 className="page-heading">Add an entry</h1>
                        <form onSubmit={handleSubmit} className="entry-form">
                            <div className="form-section">
                                <MoodSelector selectedMood={selectedMood} setSelectedMood={setSelectedMood} />

                                <fieldset className="activity-section">
                                    <legend className="section-legend">What have you been up to?</legend>
                                    <div className="activity-grid">
                                        {activities.map(activity => (
                                            <div key={activity.id} className="activity-option">
                                                <input
                                                    type="checkbox"
                                                    id={`activity-${activity.id}`}
                                                    checked={selectedActivities.includes(activity.id)}
                                                    onChange={() => toggleActivity(activity.id)}
                                                    className="activity-input"
                                                />
                                                <label 
                                                    htmlFor={`activity-${activity.id}`}
                                                    className={`activity-label ${selectedActivities.includes(activity.id) ? 'selected' : ''}`}
                                                >
                                                    <span className="activity-icon">{activity.icon}</span>
                                                    <span className="activity-name">{activity.name}</span>
                                                </label>
                                            </div>
                                        ))}
                                    </div>
                                </fieldset>
                            </div>
                            <button type="submit" className="btn-primary submit-btn" disabled={selectedMood === null}>
                                Submit
                            </button>
                        </form>
                    </div>
                ) : (
                    <div className="entries-container">
                        {entries.length === 0 ? (
                            <div className="empty-state">
                                <h2>No entries yet</h2>
                                <p>Start tracking your mood by adding your first entry!</p>
                                <button 
                                    className="btn-primary"
                                    onClick={() => setShowForm(true)}
                                >
                                    Add Your First Entry
                                </button>
                            </div>
                        ) : (
                            <div className="entries-grid">
                                {entries.map(entry => {
                                    const { day, time } = formatDate(entry.created_at);
                                    const color = getColor(entry.mood_level);
                                    const mood = moods.find(m => m.id === entry.mood_level);
                                    
                                    return (
                                        <div key={entry.id} className="entry-card">
                                            <div className={`entry-header bg-${color}`}>
                                                <p className="entry-date">{day}</p>
                                            </div>
                                            <div className="entry-body">
                                                <div className="entry-mood">
                                                    <i className={`mood-display ${mood?.icon} mood-${entry.mood_level}`}></i>
                                                    <p className="entry-time">{time}</p>
                                                </div>
                                                {entry.activities.length > 0 && (
                                                    <div className="entry-activities">
                                                        {entry.activities.map(activityId => {
                                                            const activity = activities.find(a => a.id === activityId);
                                                            return (
                                                                <span key={activityId} className="activity-tag">
                                                                    {activity?.icon}
                                                                </span>
                                                            );
                                                        })}
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </div>
                )}
            </main>
        </div>
    );
} 