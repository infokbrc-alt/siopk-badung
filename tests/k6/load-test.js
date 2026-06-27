import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Rate, Trend } from 'k6/metrics';

// ─────────────────────────────────────────────
// K6 LOAD TEST — SIOPK Badung
// ─────────────────────────────────────────────
// Usage:
//   k6 run tests/k6/load-test.js       # default: 50 VUs, 5 min
//   k6 run --env BASE_URL=https://siopk.badungkab.go.id tests/k6/load-test.js
//   k6 run --env VUS=100 --env DURATION=10m tests/k6/load-test.js
// ─────────────────────────────────────────────

const BASE_URL = __ENV.BASE_URL || 'http://localhost';
const VUS = parseInt(__ENV.VUS) || 50;
const DURATION = __ENV.DURATION || '5m';

const errorRate = new Rate('errors');
const petaLatency = new Trend('peta_latency');
const daftarLatency = new Trend('daftar_latency');
const laporLatency = new Trend('lapor_latency');

export const options = {
  stages: [
    { duration: '1m',  target: Math.floor(VUS * 0.3) },  // ramp-up
    { duration: '3m',  target: VUS },                     // steady
    { duration: '1m',  target: 0 },                       // ramp-down
  ],
  thresholds: {
    http_req_duration: ['p(95)<2000'],     // P95 < 2s
    http_req_failed:    ['rate<0.01'],     // error rate < 1%
    errors:             ['rate<0.01'],
  },
};

export default function () {
  group('Halaman Dashboard Publik', () => {
    const res = http.get(`${BASE_URL}/`);
    check(res, {
      'status 200': (r) => r.status === 200,
      'konten dashboard': (r) => r.body.includes('OPK'),
    });
  });

  group('Peta Data JSON', () => {
    const res = http.get(`${BASE_URL}/peta/data`);
    petaLatency.add(res.timings.duration);
    check(res, {
      'status 200': (r) => r.status === 200,
      'valid JSON':  (r) => {
        try { JSON.parse(r.body); return true; } catch { return false; }
      },
    });
    errorRate.add(res.status !== 200);
  });

  group('Daftar OPK', () => {
    const res = http.get(`${BASE_URL}/daftar-opk`);
    daftarLatency.add(res.timings.duration);
    check(res, {
      'status 200': (r) => r.status === 200,
    });
  });

  group('Daftar OPK (dengan filter)', () => {
    const res = http.get(`${BASE_URL}/daftar-opk?kondisi=kritis&urut=kritis`);
    daftarLatency.add(res.timings.duration);
    check(res, {
      'status 200': (r) => r.status === 200,
    });
  });

  group('API Desa Dinas', () => {
    const res = http.get(`${BASE_URL}/api/desa-dinas?kecamatan_id=1`);
    check(res, {
      'status 200': (r) => r.status === 200,
    });
  });

  group('Form Lapor (GET)', () => {
    const res = http.get(`${BASE_URL}/lapor`);
    laporLatency.add(res.timings.duration);
    check(res, {
      'status 200': (r) => r.status === 200,
      'ada form':  (r) => r.body.includes('nama_opk') || r.body.includes('form'),
    });
  });

  sleep(1);
}

export function handleSummary(data) {
  return {
    'tests/k6/summary.json': JSON.stringify(data, null, 2),
    stdout: `
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  SIOPK Badung — Load Test Summary
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  URL           : ${BASE_URL}
  VUs           : ${VUS}
  Duration      : ${DURATION}
  Total Requests: ${data.metrics.http_reqs?.values?.count || 0}
  Failed        : ${data.metrics.http_req_failed?.values?.rate ? (data.metrics.http_req_failed.values.rate * 100).toFixed(2) : 0}%
  P95 Latency   : ${data.metrics.http_req_duration?.values ? data.metrics.http_req_duration.values['p(95)'].toFixed(0) : 0}ms
  P99 Latency   : ${data.metrics.http_req_duration?.values ? data.metrics.http_req_duration.values['p(99)'].toFixed(0) : 0}ms
  Avg Peta JSON : ${data.metrics.peta_latency?.values ? data.metrics.peta_latency.values.avg.toFixed(0) : 0}ms
  Avg Daftar OPK: ${data.metrics.daftar_latency?.values ? data.metrics.daftar_latency.values.avg.toFixed(0) : 0}ms
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
`,
  };
}
