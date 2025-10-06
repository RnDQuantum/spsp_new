# 📡 API SPECIFICATION - CI3 EXPORT ENDPOINT

## Overview

API ini digunakan oleh aplikasi Laravel untuk melakukan sync data asesmen dari aplikasi utama (CodeIgniter 3).

---

## Endpoint Details

### **GET** `/api/events/{event_code}/export`

**Description:** Export semua data event beserta participants dan scores

**Authentication:** API Key via Header

**Headers:**
```
X-API-Key: {shared_secret_key}
Content-Type: application/json
```

**URL Parameters:**
- `event_code` (string, required) - Event code yang akan di-export
  - Example: `P3K-KEJAKSAAN-2025`

**Success Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "institution": {
      "code": "kejaksaan",
      "name": "Kejaksaan Agung RI",
      "logo_url": "https://example.com/logo.png"
    },

    "template": {
      "code": "p3k_standard_2025",
      "name": "Template P3K Standard 2025",
      "description": "Template standar untuk P3K 2025",
      "categories": [
        {
          "code": "potensi",
          "name": "Potensi",
          "weight": 40,
          "order": 1,
          "aspects": [
            {
              "code": "kecerdasan",
              "name": "Kecerdasan",
              "weight": 30,
              "standard_rating": 3.50,
              "order": 1,
              "sub_aspects": [
                {
                  "code": "kecerdasan_umum",
                  "name": "Kecerdasan Umum",
                  "standard_rating": 3,
                  "order": 1
                },
                {
                  "code": "daya_tangkap",
                  "name": "Daya Tangkap",
                  "standard_rating": 4,
                  "order": 2
                },
                {
                  "code": "kemampuan_analisa",
                  "name": "Kemampuan Analisa",
                  "standard_rating": 4,
                  "order": 3
                },
                {
                  "code": "berpikir_konseptual",
                  "name": "Berpikir Konseptual",
                  "standard_rating": 3,
                  "order": 4
                },
                {
                  "code": "logika_berpikir",
                  "name": "Logika Berpikir",
                  "standard_rating": 4,
                  "order": 5
                },
                {
                  "code": "kemampuan_numerik",
                  "name": "Kemampuan Numerik",
                  "standard_rating": 3,
                  "order": 6
                }
              ]
            },
            {
              "code": "sikap_kerja",
              "name": "Sikap Kerja",
              "weight": 20,
              "standard_rating": 3.09,
              "order": 2,
              "sub_aspects": [
                {
                  "code": "sistematika_kerja",
                  "name": "Sistematika Kerja",
                  "standard_rating": 3,
                  "order": 1
                },
                {
                  "code": "perhatian_terhadap_detail",
                  "name": "Perhatian Terhadap Detail",
                  "standard_rating": 3,
                  "order": 2
                },
                {
                  "code": "ketekunan_kerja",
                  "name": "Ketekunan Kerja",
                  "standard_rating": 3,
                  "order": 3
                },
                {
                  "code": "kerjasama",
                  "name": "Kerjasama",
                  "standard_rating": 4,
                  "order": 4
                },
                {
                  "code": "tanggung_jawab",
                  "name": "Tanggung Jawab",
                  "standard_rating": 4,
                  "order": 5
                },
                {
                  "code": "dorongan_berprestasi",
                  "name": "Dorongan Berprestasi",
                  "standard_rating": 4,
                  "order": 6
                },
                {
                  "code": "inisiatif",
                  "name": "Inisiatif",
                  "standard_rating": 3,
                  "order": 7
                }
              ]
            },
            {
              "code": "hubungan_sosial",
              "name": "Hubungan Sosial",
              "weight": 20,
              "standard_rating": 2.70,
              "order": 3,
              "sub_aspects": [
                {
                  "code": "kepekaan_interpersonal",
                  "name": "Kepekaan Interpersonal",
                  "standard_rating": 3,
                  "order": 1
                },
                {
                  "code": "komunikasi",
                  "name": "Komunikasi",
                  "standard_rating": 3,
                  "order": 2
                },
                {
                  "code": "hubungan_interpersonal",
                  "name": "Hubungan Interpersonal",
                  "standard_rating": 3,
                  "order": 3
                },
                {
                  "code": "penyesuaian_diri",
                  "name": "Penyesuaian Diri",
                  "standard_rating": 3,
                  "order": 4
                }
              ]
            },
            {
              "code": "kepribadian",
              "name": "Kepribadian",
              "weight": 30,
              "standard_rating": 3.00,
              "order": 4,
              "sub_aspects": [
                {
                  "code": "stabilitas_kematangan_emosi",
                  "name": "Stabilitas/Kematangan Emosi",
                  "standard_rating": 3,
                  "order": 1
                },
                {
                  "code": "agility",
                  "name": "Agility",
                  "standard_rating": 3,
                  "order": 2
                },
                {
                  "code": "kepercayaan_diri",
                  "name": "Kepercayaan Diri",
                  "standard_rating": 3,
                  "order": 3
                },
                {
                  "code": "daya_tahan_stress",
                  "name": "Daya Tahan Stress",
                  "standard_rating": 4,
                  "order": 4
                },
                {
                  "code": "kepemimpinan",
                  "name": "Kepemimpinan",
                  "standard_rating": 4,
                  "order": 5
                },
                {
                  "code": "loyalitas",
                  "name": "Loyalitas",
                  "standard_rating": 3,
                  "order": 6
                }
              ]
            }
          ]
        },
        {
          "code": "kompetensi",
          "name": "Kompetensi",
          "weight": 60,
          "order": 2,
          "aspects": [
            {
              "code": "integritas",
              "name": "Integritas",
              "weight": 12,
              "standard_rating": 2.70,
              "order": 1,
              "sub_aspects": []
            },
            {
              "code": "kerjasama",
              "name": "Kerjasama",
              "weight": 11,
              "standard_rating": 2.70,
              "order": 2,
              "sub_aspects": []
            },
            {
              "code": "komunikasi",
              "name": "Komunikasi",
              "weight": 11,
              "standard_rating": 2.70,
              "order": 3,
              "sub_aspects": []
            },
            {
              "code": "orientasi_pada_hasil",
              "name": "Orientasi Pada Hasil",
              "weight": 11,
              "standard_rating": 2.70,
              "order": 4,
              "sub_aspects": []
            },
            {
              "code": "pelayanan_publik",
              "name": "Pelayanan Publik",
              "weight": 11,
              "standard_rating": 2.70,
              "order": 5,
              "sub_aspects": []
            },
            {
              "code": "pengembangan_diri_orang_lain",
              "name": "Pengembangan Diri & Orang Lain",
              "weight": 11,
              "standard_rating": 2.70,
              "order": 6,
              "sub_aspects": []
            },
            {
              "code": "mengelola_perubahan",
              "name": "Mengelola Perubahan",
              "weight": 11,
              "standard_rating": 2.70,
              "order": 7,
              "sub_aspects": []
            },
            {
              "code": "pengambilan_keputusan",
              "name": "Pengambilan Keputusan",
              "weight": 11,
              "standard_rating": 2.70,
              "order": 8,
              "sub_aspects": []
            },
            {
              "code": "perekat_bangsa",
              "name": "Perekat Bangsa",
              "weight": 11,
              "standard_rating": 2.70,
              "order": 9,
              "sub_aspects": []
            }
          ]
        }
      ]
    },

    "event": {
      "code": "P3K-KEJAKSAAN-2025",
      "name": "Asesmen P3K Kejaksaan Agung RI 2025",
      "year": 2025,
      "start_date": "2025-09-01",
      "end_date": "2025-12-31",
      "status": "completed"
    },

    "batches": [
      {
        "code": "BATCH-1-MOJOKERTO",
        "name": "Gelombang 1 - Mojokerto",
        "location": "Mojokerto",
        "batch_number": 1,
        "start_date": "2025-09-27",
        "end_date": "2025-09-28"
      }
    ],

    "positions": [
      {
        "code": "fisikawan_medis",
        "name": "Fisikawan Medis Ahli Pertama",
        "quota": 10
      }
    ],

    "participants": [
      {
        "test_number": "03-5-2-18-001",
        "skb_number": "24400240120012571",
        "name": "EKA FEBRIYANI, s.si",
        "position_code": "fisikawan_medis",
        "batch_code": "BATCH-1-MOJOKERTO",
        "assessment_date": "2025-09-27",
        "photo_url": "https://example.com/photos/03-5-2-18-001.jpg",

        "assessments": {
          "potensi": {
            "total_standard_rating": 11.94,
            "total_standard_score": 300.21,
            "total_individual_rating": 11.83,
            "total_individual_score": 294.25,
            "gap_rating": -0.11,
            "gap_score": -5.97,
            "conclusion": "DI BAWAH STANDARD",

            "aspects": [
              {
                "aspect_code": "kecerdasan",
                "standard_rating": 3.15,
                "standard_score": 94.50,
                "individual_rating": 2.58,
                "individual_score": 77.29,
                "gap_rating": -0.57,
                "gap_score": -17.21,
                "percentage": 78,
                "conclusion": "Kurang Memenuhi Standard",

                "sub_aspects": [
                  {
                    "sub_aspect_code": "kecerdasan_umum",
                    "standard_rating": 3,
                    "individual_rating": 3,
                    "rating_label": "Cukup"
                  },
                  {
                    "sub_aspect_code": "daya_tangkap",
                    "standard_rating": 4,
                    "individual_rating": 4,
                    "rating_label": "Baik"
                  },
                  {
                    "sub_aspect_code": "kemampuan_analisa",
                    "standard_rating": 4,
                    "individual_rating": 4,
                    "rating_label": "Baik"
                  },
                  {
                    "sub_aspect_code": "berpikir_konseptual",
                    "standard_rating": 3,
                    "individual_rating": 3,
                    "rating_label": "Cukup"
                  },
                  {
                    "sub_aspect_code": "logika_berpikir",
                    "standard_rating": 4,
                    "individual_rating": 4,
                    "rating_label": "Baik"
                  },
                  {
                    "sub_aspect_code": "kemampuan_numerik",
                    "standard_rating": 3,
                    "individual_rating": 3,
                    "rating_label": "Cukup"
                  }
                ]
              }
            ]
          },

          "kompetensi": {
            "total_standard_rating": 24.30,
            "total_standard_score": 270.00,
            "total_individual_rating": 27.48,
            "total_individual_score": 305.36,
            "gap_rating": 3.18,
            "gap_score": 35.36,
            "conclusion": "SANGAT KOMPETEN",

            "aspects": [
              {
                "aspect_code": "integritas",
                "standard_rating": 2.70,
                "standard_score": 32.40,
                "individual_rating": 3.08,
                "individual_score": 36.96,
                "gap_rating": 0.38,
                "gap_score": 4.56,
                "conclusion": "Sangat Memenuhi Standard",
                "description": "Individu kompeten menampilkan kompetensi integritas sesuai dengan standar level yang di tetapkan. Secara konsisten mampu mengingatkan dan mengajak rekan kerja untuk bertindak sesuai dengan etika dan kode etik. Hal ini tentunya akan memberikan dukungan terhadap peran tugasnya sesuai dengan formasi yang dituju.",
                "sub_aspects": []
              },
              {
                "aspect_code": "kerjasama",
                "standard_rating": 2.70,
                "standard_score": 29.70,
                "individual_rating": 2.90,
                "individual_score": 31.90,
                "gap_rating": 0.20,
                "gap_score": 2.20,
                "conclusion": "Masih Memenuhi Standard",
                "description": "Kemampuan menumbuhkan tim kerja cukup kompeten...",
                "sub_aspects": []
              }
            ]
          }
        },

        "final_result": {
          "potensi_weight": 40,
          "potensi_standard_score": 133.43,
          "potensi_individual_score": 117.70,
          "kompetensi_weight": 60,
          "kompetensi_standard_score": 180.00,
          "kompetensi_individual_score": 183.22,
          "total_standard_score": 313.43,
          "total_individual_score": 300.91,
          "achievement_percentage": 96.01,
          "conclusion": "MASIH MEMENUHI SYARAT (MMS)"
        },

        "psychological_test": {
          "raw_score": 40.00,
          "iq_score": 97,
          "validity_status": "Hasil tes ini konsisten, tetapi kurang akurat dan kurang dapat dipercaya, karena klien cenderung menjawab lebih bagus dari keadaan yang sebenarnya.",
          "internal_status": "Kurang terbuka",
          "interpersonal_status": "Kurang terbuka",
          "work_capacity_status": "Kurang terbuka",
          "clinical_status": "Kurang terbuka",
          "conclusion": "TIDAK MEMENUHI SYARAT (TMS)",
          "notes": "Mungkin terdapat psikopatologi (gejala kejiwaan) yang disembunyikan."
        },

        "interpretations": {
          "potensi": "Memiliki kepekaan yang cukup memadai dalam memahami kebutuhan orang-orang yang ada di sekitarnya. Individu berusaha untuk memenuhi kebutuhan yang diperlukan oleh orang yang ada di sekitarnya, terutama yang menjadi kebutuhan kelompoknya...",
          "kompetensi": "Dalam bekerja, individu cukup mampu mengelola pekerjaan yang menjadi tanggung jawabnya sesuai dengan prioritas penyelesaian masalah sehingga dapat selesai sesuai tenggat waktunya..."
        }
      }
    ]
  },

  "meta": {
    "total_participants": 150,
    "synced_at": "2025-10-05 14:30:00"
  }
}
```

---

## Error Responses

### **401 Unauthorized**
Invalid API Key

```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Invalid API Key"
  }
}
```

### **404 Not Found**
Event tidak ditemukan

```json
{
  "success": false,
  "error": {
    "code": "EVENT_NOT_FOUND",
    "message": "Event with code 'P3K-KEJAKSAAN-2025' not found"
  }
}
```

### **500 Internal Server Error**
Server error

```json
{
  "success": false,
  "error": {
    "code": "INTERNAL_ERROR",
    "message": "An error occurred while processing your request"
  }
}
```

---

## Notes

1. **Nested Structure**: Template structure bersifat dynamic, bisa berbeda per event
2. **Sub-Aspects**: Kompetensi tidak punya sub-aspects (empty array)
3. **Interpretations**: Bisa per category (potensi/kompetensi) atau general (null)
4. **Photo URL**: Optional, bisa null jika tidak ada foto
5. **Batch Code**: Optional, peserta bisa tidak masuk batch tertentu (null)

---

## Testing

**Example cURL:**

```bash
curl -X GET "http://ci3-app.local/api/events/P3K-KEJAKSAAN-2025/export" \
  -H "X-API-Key: your-secret-api-key-here" \
  -H "Accept: application/json"
```

**Example Response Time:**
- 50 participants: ~2-5 seconds
- 150 participants: ~5-10 seconds
- 500 participants: ~15-30 seconds

---

## Implementation Checklist (CI3 Side)

- [ ] Create API controller
- [ ] Implement API key validation
- [ ] Create query untuk get event + template structure
- [ ] Create query untuk get all participants
- [ ] Create query untuk get all assessments (category, aspect, sub-aspect)
- [ ] Create query untuk get final assessments
- [ ] Create query untuk get psychological tests
- [ ] Create query untuk get interpretations
- [ ] Structure JSON response sesuai format di atas
- [ ] Add error handling
- [ ] Test dengan Postman/Insomnia
- [ ] Optimize query performance (N+1 problem)
- [ ] Add request logging
