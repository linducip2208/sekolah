/**
 * eSchool SaaS — Offline Database (IndexedDB)
 * Menyimpan data offline: absensi, daftar siswa (basic), input nilai
 */
(function () {
    'use strict';

    const DB_NAME = 'eschool-offline';
    const DB_VERSION = 1;

    let db = null;

    function openDb() {
        return new Promise((resolve, reject) => {
            if (db) return resolve(db);

            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onupgradeneeded = function (event) {
                const database = event.target.result;

                if (!database.objectStoreNames.contains('pendingQueue')) {
                    const queueStore = database.createObjectStore('pendingQueue', {
                        keyPath: 'id',
                        autoIncrement: true,
                    });
                    queueStore.createIndex('type', 'type', { unique: false });
                    queueStore.createIndex('createdAt', 'createdAt', { unique: false });
                }

                if (!database.objectStoreNames.contains('studentCache')) {
                    const studentStore = database.createObjectStore('studentCache', {
                        keyPath: 'id',
                    });
                    studentStore.createIndex('name', 'name', { unique: false });
                }

                if (!database.objectStoreNames.contains('attendanceCache')) {
                    const attStore = database.createObjectStore('attendanceCache', {
                        keyPath: 'id',
                    });
                    attStore.createIndex('date', 'date', { unique: false });
                    attStore.createIndex('studentId', 'student_id', { unique: false });
                }

                if (!database.objectStoreNames.contains('marksCache')) {
                    const marksStore = database.createObjectStore('marksCache', {
                        keyPath: 'id',
                    });
                    marksStore.createIndex('studentId', 'student_id', { unique: false });
                }
            };

            request.onsuccess = function (event) {
                db = event.target.result;
                resolve(db);
            };

            request.onerror = function (event) {
                console.error('[OfflineDB] Gagal membuka IndexedDB:', event.target.error);
                reject(event.target.error);
            };
        });
    }

    window.OfflineDB = {
        async addToQueue(record) {
            const database = await openDb();
            return new Promise((resolve, reject) => {
                const tx = database.transaction('pendingQueue', 'readwrite');
                const store = tx.objectStore('pendingQueue');
                const item = {
                    type: record.type,
                    payload: record.payload,
                    createdAt: new Date().toISOString(),
                    retries: 0,
                };
                const request = store.add(item);
                request.onsuccess = () => resolve(request.result);
                request.onerror = () => reject(request.error);
            });
        },

        async getPendingQueue() {
            const database = await openDb();
            return new Promise((resolve, reject) => {
                const tx = database.transaction('pendingQueue', 'readonly');
                const store = tx.objectStore('pendingQueue');
                const request = store.getAll();
                request.onsuccess = () => resolve(request.result || []);
                request.onerror = () => reject(request.error);
            });
        },

        async removeFromQueue(id) {
            const database = await openDb();
            return new Promise((resolve, reject) => {
                const tx = database.transaction('pendingQueue', 'readwrite');
                const store = tx.objectStore('pendingQueue');
                const request = store.delete(id);
                request.onsuccess = () => resolve();
                request.onerror = () => reject(request.error);
            });
        },

        async clearQueue() {
            const database = await openDb();
            return new Promise((resolve, reject) => {
                const tx = database.transaction('pendingQueue', 'readwrite');
                const store = tx.objectStore('pendingQueue');
                const request = store.clear();
                request.onsuccess = () => resolve();
                request.onerror = () => reject(request.error);
            });
        },

        async getQueueCount() {
            const database = await openDb();
            return new Promise((resolve, reject) => {
                const tx = database.transaction('pendingQueue', 'readonly');
                const store = tx.objectStore('pendingQueue');
                const request = store.count();
                request.onsuccess = () => resolve(request.result);
                request.onerror = () => reject(request.error);
            });
        },

        async cacheStudents(students) {
            const database = await openDb();
            return new Promise((resolve, reject) => {
                const tx = database.transaction('studentCache', 'readwrite');
                const store = tx.objectStore('studentCache');
                store.clear();
                for (const s of students) {
                    store.put({ id: s.id, name: s.name || (s.user?.name), admission_no: s.admission_no, class_section_id: s.class_section_id });
                }
                tx.oncomplete = () => resolve();
                tx.onerror = () => reject(tx.error);
            });
        },

        async getCachedStudents() {
            const database = await openDb();
            return new Promise((resolve, reject) => {
                const tx = database.transaction('studentCache', 'readonly');
                const store = tx.objectStore('studentCache');
                const request = store.getAll();
                request.onsuccess = () => resolve(request.result || []);
                request.onerror = () => reject(request.error);
            });
        },

        async cacheAttendanceRecords(records, date) {
            const database = await openDb();
            return new Promise((resolve, reject) => {
                const tx = database.transaction('attendanceCache', 'readwrite');
                const store = tx.objectStore('attendanceCache');
                for (const r of records) {
                    store.put({
                        id: 'offline_' + r.student_id + '_' + date,
                        student_id: r.student_id,
                        date: date,
                        status: r.status,
                        note: r.note || '',
                        class_section_id: r.class_section_id,
                    });
                }
                tx.oncomplete = () => resolve();
                tx.onerror = () => reject(tx.error);
            });
        },

        async getCachedAttendance(date) {
            const database = await openDb();
            return new Promise((resolve, reject) => {
                const tx = database.transaction('attendanceCache', 'readonly');
                const store = tx.objectStore('attendanceCache');
                const index = store.index('date');
                const request = index.getAll(IDBKeyRange.only(date));
                request.onsuccess = () => resolve(request.result || []);
                request.onerror = () => reject(request.error);
            });
        },

        async cacheMarks(marks) {
            const database = await openDb();
            return new Promise((resolve, reject) => {
                const tx = database.transaction('marksCache', 'readwrite');
                const store = tx.objectStore('marksCache');
                for (const m of marks) {
                    store.put({
                        id: 'offline_' + m.student_id + '_' + m.subject_id + '_' + m.exam_id,
                        student_id: m.student_id,
                        subject_id: m.subject_id,
                        exam_id: m.exam_id,
                        semester_id: m.semester_id,
                        obtained_marks: m.obtained_marks,
                        total_marks: m.total_marks,
                        grade: m.grade || '',
                    });
                }
                tx.oncomplete = () => resolve();
                tx.onerror = () => reject(tx.error);
            });
        },

        async getCachedMarks() {
            const database = await openDb();
            return new Promise((resolve, reject) => {
                const tx = database.transaction('marksCache', 'readonly');
                const store = tx.objectStore('marksCache');
                const request = store.getAll();
                request.onsuccess = () => resolve(request.result || []);
                request.onerror = () => reject(request.error);
            });
        },
    };
})();
