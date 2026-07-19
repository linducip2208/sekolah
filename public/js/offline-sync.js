/**
 * eSchool SaaS — Offline Sync Manager
 * Memonitor status online/offline, auto-sync saat reconnect.
 * Conflict resolution: server-wins for attendance (timestamp), last-write-wins for marks.
 */
(function () {
    'use strict';

    const SYNC_ENDPOINT = '/api/v1/sync/batch';
    const CHECK_URL = '/api/v1/health';
    const SYNC_INTERVAL = 30000;
    const RECONNECT_DELAY = 5000;

    let isOnline = navigator.onLine;
    let syncTimer = null;
    let reconnectCheckTimer = null;

    function updateOnlineStatus() {
        const wasOffline = !isOnline;
        isOnline = navigator.onLine;

        window.dispatchEvent(new CustomEvent('eschool:online-change', {
            detail: { online: isOnline },
        }));

        if (isOnline && wasOffline) {
            console.log('[OfflineSync] Terhubung kembali — memulai sinkronisasi...');
            setTimeout(() => flushQueue(), RECONNECT_DELAY);
            if (reconnectCheckTimer) { clearInterval(reconnectCheckTimer); reconnectCheckTimer = null; }
        }

        if (!isOnline) {
            startReconnectCheck();
        }
    }

    function startReconnectCheck() {
        if (reconnectCheckTimer) return;
        reconnectCheckTimer = setInterval(async () => {
            try {
                const controller = new AbortController();
                const timeout = setTimeout(() => controller.abort(), 5000);
                const res = await fetch(CHECK_URL, { signal: controller.signal, cache: 'no-store' });
                clearTimeout(timeout);
                if (res.ok && !isOnline) {
                    isOnline = true;
                    window.dispatchEvent(new CustomEvent('eschool:online-change', {
                        detail: { online: true },
                    }));
                    console.log('[OfflineSync] Terhubung kembali (periodic check) — memulai sinkronisasi...');
                    clearInterval(reconnectCheckTimer);
                    reconnectCheckTimer = null;
                    setTimeout(() => flushQueue(), 1000);
                }
            } catch (e) {}
        }, 15000);
    }

    async function flushQueue() {
        const queue = await OfflineDB.getPendingQueue();
        if (queue.length === 0) {
            console.log('[OfflineSync] Antrian kosong.');
            return { processed: 0, failed: 0 };
        }

        console.log(`[OfflineSync] Memproses ${queue.length} record dari antrian...`);

        const records = queue.map(q => ({
            local_id: String(q.id),
            ...q.payload,
        }));

        try {
            const token = getCsrfToken();
            const res = await fetch(SYNC_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({ records }),
            });

            const data = await res.json();

            if (data.results) {
                for (const result of data.results) {
                    if (result.status === 'processed') {
                        const queueId = parseInt(result.local_id);
                        if (!isNaN(queueId)) {
                            await OfflineDB.removeFromQueue(queueId);
                        }
                    }
                }
            }

            const remaining = await OfflineDB.getQueueCount();
            console.log(`[OfflineSync] Selesai: ${data.processed} diproses, ${data.failed} gagal. Tersisa: ${remaining}`);

            window.dispatchEvent(new CustomEvent('eschool:sync-complete', {
                detail: data,
            }));

            return data;
        } catch (e) {
            console.error('[OfflineSync] Gagal sinkronisasi:', e.message);
            return { processed: 0, failed: queue.length, error: e.message };
        }
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    async function enqueueAttendance(studentId, classSectionId, date, status, note) {
        await OfflineDB.addToQueue({
            type: 'attendance',
            payload: {
                type: 'attendance',
                student_id: studentId,
                class_section_id: classSectionId,
                date: date,
                status: status,
                note: note || '',
            },
        });

        await OfflineDB.cacheAttendanceRecords([{
            student_id: studentId,
            class_section_id: classSectionId,
            date: date,
            status: status,
            note: note || '',
        }], date);

        const count = await OfflineDB.getQueueCount();
        window.dispatchEvent(new CustomEvent('eschool:queue-changed', {
            detail: { count },
        }));
    }

    async function enqueueMarks(studentId, subjectId, examId, semesterId, obtainedMarks, totalMarks, grade) {
        await OfflineDB.addToQueue({
            type: 'mark',
            payload: {
                type: 'mark',
                student_id: studentId,
                subject_id: subjectId,
                exam_id: examId,
                semester_id: semesterId,
                obtained_marks: obtainedMarks,
                total_marks: totalMarks,
                grade: grade || '',
            },
        });

        await OfflineDB.cacheMarks([{
            student_id: studentId,
            subject_id: subjectId,
            exam_id: examId,
            semester_id: semesterId,
            obtained_marks: obtainedMarks,
            total_marks: totalMarks,
            grade: grade || '',
        }]);

        const count = await OfflineDB.getQueueCount();
        window.dispatchEvent(new CustomEvent('eschool:queue-changed', {
            detail: { count },
        }));
    }

    function startAutoSync() {
        if (syncTimer) clearInterval(syncTimer);
        syncTimer = setInterval(async () => {
            if (isOnline || navigator.onLine) {
                const count = await OfflineDB.getQueueCount();
                if (count > 0) {
                    console.log(`[OfflineSync] Auto-sync: ${count} record menunggu.`);
                    await flushQueue();
                }
            }
        }, SYNC_INTERVAL);
    }

    window.addEventListener('online', () => {
        isOnline = true;
        updateOnlineStatus();
    });

    window.addEventListener('offline', () => {
        isOnline = false;
        updateOnlineStatus();
    });

    document.addEventListener('DOMContentLoaded', () => {
        updateOnlineStatus();
        startAutoSync();
    });

    window.EschoolSync = {
        flushQueue,
        enqueueAttendance,
        enqueueMarks,
        isOnline: () => isOnline || navigator.onLine,
    };
})();
