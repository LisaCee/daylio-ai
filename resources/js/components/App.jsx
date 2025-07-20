import React, { useState } from 'react';
import ActivitySelector, { activities } from './activities/ActivitySelector';
import './App.css';
import MoodSelector, { moods } from './mood/MoodSelector';
import Navigation from './navigation/Navigation';

export default function App() {
    const [selectedMood, setSelectedMood] = useState(null);
    const [selectedActivities, setSelectedActivities] = useState([]);
    const [note, setNote] = useState('');
    const [entries, setEntries] = useState([]);

    const handleMoodSelect = (moodId) => {
        setSelectedMood(moodId);
    };

    const handleActivityToggle = (activityId) => {
        setSelectedActivities(prev => 
            prev.includes(activityId) 
                ? prev.filter(id => id !== activityId)
                : [...prev, activityId]
        );
    };

    const handleSubmit = () => {
        if (!selectedMood) return;

        const newEntry = {
            id: Date.now(),
            mood: selectedMood,
            activities: selectedActivities,
            note,
            timestamp: new Date()
        };

        setEntries(prev => [newEntry, ...prev]);
        setSelectedMood(null);
        setSelectedActivities([]);
        setNote('');
    };

    const getMoodById = (id) => moods.find(mood => mood.id === id);
    const getActivityById = (id) => activities.find(activity => activity.id === id);

    return (
        <div className="app">
            <Navigation />
            <main className="main-content">
                <div className="mood-tracker">
                    <h1>How are you feeling?</h1>
                    
                    <MoodSelector 
                        selectedMood={selectedMood} 
                        onMoodSelect={handleMoodSelect} 
                    />
                    
                    <ActivitySelector 
                        selectedActivities={selectedActivities}
                        onActivityToggle={handleActivityToggle}
                    />
                    
                    <div className="note-section">
                        <h3>Note (optional)</h3>
                        <textarea
                            value={note}
                            onChange={(e) => setNote(e.target.value)}
                            placeholder="How was your day?"
                            className="note-input"
                        />
                    </div>
                    
                    <button 
                        onClick={handleSubmit}
                        disabled={!selectedMood}
                        className="submit-btn"
                    >
                        Save Entry
                    </button>
                </div>

                <div className="entries-section">
                    <h2>Recent Entries</h2>
                    {entries.map(entry => {
                        const mood = getMoodById(entry.mood);
                        return (
                            <div key={entry.id} className="entry-card">
                                <div className="entry-header">
                                    <div className="mood-info">
                                        <i className={mood.icon}></i>
                                        <span className="mood-name">{mood.name}</span>
                                    </div>
                                    <span className="entry-time">
                                        {entry.timestamp.toLocaleTimeString([], { 
                                            hour: '2-digit', 
                                            minute: '2-digit' 
                                        })}
                                    </span>
                                </div>
                                
                                {entry.activities.length > 0 && (
                                    <div className="activities-display">
                                        {entry.activities.map(activityId => {
                                            const activity = getActivityById(activityId);
                                            return (
                                                <span key={activityId} className="activity-tag">
                                                    <i className={activity?.icon}></i>
                                                </span>
                                            );
                                        })}
                                    </div>
                                )}
                                
                                {entry.note && (
                                    <p className="entry-note">{entry.note}</p>
                                )}
                            </div>
                        );
                    })}
                </div>
            </main>
        </div>
    );
} 