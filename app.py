import sqlite3
from pathlib import Path

import pandas as pd
import streamlit as st


DB_PATH = Path(__file__).with_name("votes.db")
PIN_PANITIA = "1234"

CANDIDATES = [
    {"id": 1, "name": "Bpk. Dimas", "address": "Blok F 15"},
    {"id": 2, "name": "Bpk. Abdul Rohman", "address": "Blok F 12A"},
    {"id": 3, "name": "Bpk. Catur", "address": "Blok G 1"},
    {"id": 4, "name": "Bpk. Miskan", "address": "Blok C 37"},
    {"id": 5, "name": "Bpk. Iwan", "address": "Blok C 06"},
    {"id": 6, "name": "Bpk. Winarno", "address": "Blok C 15"},
    {"id": 7, "name": "Bpk. Joko Waluyo", "address": "Blok D 24"},
    {"id": 8, "name": "Bpk. Budi", "address": "Blok F 4"},
    {"id": 9, "name": "Bpk. Caswan", "address": "Blok C 14"},
    {"id": "tidak-memilih", "name": "Tidak Memilih", "address": ""},
]


def get_connection():
    connection = sqlite3.connect(DB_PATH, check_same_thread=False)
    connection.execute("PRAGMA busy_timeout = 5000")
    return connection


def initialize_database():
    with get_connection() as connection:
        connection.execute(
            """
            CREATE TABLE IF NOT EXISTS votes (
                candidate_id TEXT PRIMARY KEY,
                vote_count INTEGER NOT NULL DEFAULT 0
            )
            """
        )
        for candidate in CANDIDATES:
            connection.execute(
                "INSERT OR IGNORE INTO votes (candidate_id, vote_count) VALUES (?, 0)",
                (str(candidate["id"]),),
            )


def get_vote_counts():
    with get_connection() as connection:
        rows = connection.execute(
            "SELECT candidate_id, vote_count FROM votes"
        ).fetchall()
    counts = {candidate_id: count for candidate_id, count in rows}
    return counts


def add_vote(candidate_id):
    with get_connection() as connection:
        connection.execute(
            """
            UPDATE votes
            SET vote_count = vote_count + 1
            WHERE candidate_id = ?
            """,
            (str(candidate_id),),
        )


def reset_votes():
    with get_connection() as connection:
        connection.execute("UPDATE votes SET vote_count = 0")


@st.dialog("Konfirmasi Pilihan")
def confirm_vote(candidate):
    if st.session_state.get("has_voted", False):
        st.warning("Anda sudah memilih. Setiap pemilih hanya dapat memberikan satu suara.")
        return

    if candidate["id"] == "tidak-memilih":
        st.write("Apakah Anda yakin memilih: **Tidak Memilih**?")
    else:
        st.write(
            f"Apakah Anda yakin memilih:\n\n"
            f"**Calon Nomor {candidate['id']}: {candidate['name']}**?"
        )

    col1, col2 = st.columns(2)
    if col1.button("Ya, pilih", type="primary", use_container_width=True):
        add_vote(candidate["id"])
        st.session_state["last_vote"] = candidate["name"]
        st.session_state["has_voted"] = True
        st.rerun()
    if col2.button("Batal", use_container_width=True):
        st.rerun()


@st.dialog("Konfirmasi Reset")
def confirm_reset():
    st.warning("PERINGATAN: Semua data suara akan dikembalikan ke 0.")
    col1, col2 = st.columns(2)
    if col1.button("Ya, reset semua", type="primary", use_container_width=True):
        reset_votes()
        st.session_state["reset_done"] = True
        st.rerun()
    if col2.button("Batal", use_container_width=True):
        st.rerun()


st.set_page_config(
    page_title="Pemilihan Ketua RW",
    page_icon="🗳️",
    layout="wide",
)

st.markdown(
    """
    <style>
    .block-container { max-width: 1100px; padding-top: 2rem; }
    h1, .subtitle { text-align: center; }
    .subtitle { color: #64748b; margin-bottom: 1.5rem; }
    .candidate-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
        padding: 1.2rem;
        text-align: center;
        min-height: 215px;
        margin-bottom: 1rem;
    }
    .candidate-number {
        display: inline-grid;
        place-items: center;
        width: 42px;
        height: 42px;
        margin: 0 auto 0.7rem;
        border-radius: 50%;
        background: #2563eb;
        color: white;
        font-weight: 800;
        font-size: 1.05rem;
    }
    .candidate-name { font-size: 1.08rem; font-weight: 750; color: #0f172a; }
    .candidate-address { min-height: 38px; color: #475569; margin: 0.35rem 0 0.8rem; }
    .panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1.2rem;
        margin-top: 1.5rem;
    }
    </style>
    """,
    unsafe_allow_html=True,
)

initialize_database()

if "has_voted" not in st.session_state:
    st.session_state["has_voted"] = False

st.title("USULAN CALON KETUA RW 08")
st.markdown(
    '<p class="subtitle">Silakan pilih calon Ketua RW pilihan Anda</p>',
    unsafe_allow_html=True,
)

if st.session_state.pop("last_vote", None):
    st.success("Terima kasih! Suara Anda telah berhasil direkam.")

for row_start in range(0, len(CANDIDATES), 3):
    columns = st.columns(3)
    for column, candidate in zip(columns, CANDIDATES[row_start : row_start + 3]):
        with column:
            number = "-" if candidate["id"] == "tidak-memilih" else str(candidate["id"])
            address = (
                f"Alamat: {candidate['address']}"
                if candidate["address"]
                else "&nbsp;"
            )
            st.markdown(
                f"""
                <div class="candidate-card">
                    <div class="candidate-number">{number}</div>
                    <div class="candidate-name">{candidate["name"]}</div>
                    <div class="candidate-address">{address}</div>
                </div>
                """,
                unsafe_allow_html=True,
            )
            button_label = (
                "TIDAK MEMILIH"
                if candidate["id"] == "tidak-memilih"
                else f"PILIH CALON NO. {candidate['id']}"
            )
            if st.button(
                button_label,
                key=f"vote-{candidate['id']}",
                type="primary",
                use_container_width=True,
                disabled=st.session_state["has_voted"],
            ):
                confirm_vote(candidate)

st.markdown('<div class="panel">', unsafe_allow_html=True)
st.subheader("Panel Panitia")
st.caption("PIN default: 1234")

counts = get_vote_counts()
total_votes = sum(counts.values())

if "show_results" not in st.session_state:
    st.session_state["show_results"] = False

action_left, action_right = st.columns(2)
if action_left.button("Lihat / Sembunyikan Hasil Suara", use_container_width=True):
    if st.session_state["show_results"]:
        st.session_state["show_results"] = False
    else:
        pin = st.session_state.get("result_pin", "")
        if pin == PIN_PANITIA:
            st.session_state["show_results"] = True
        else:
            st.session_state["pin_action"] = "results"

if action_right.button("Reset Semua Suara", type="secondary", use_container_width=True):
    st.session_state["pin_action"] = "reset"

if st.session_state.get("pin_action") in {"results", "reset"}:
    with st.container(border=True):
        action = st.session_state["pin_action"]
        pin_label = (
            "Masukkan PIN Panitia untuk melihat hasil"
            if action == "results"
            else "Masukkan PIN Panitia untuk menghapus semua suara"
        )
        pin = st.text_input(pin_label, type="password", key="committee_pin")
        if st.button("Lanjutkan", key="verify_pin"):
            if pin == PIN_PANITIA:
                if action == "results":
                    st.session_state["show_results"] = True
                    st.session_state.pop("pin_action", None)
                    st.rerun()
                else:
                    st.session_state.pop("pin_action", None)
                    confirm_reset()
            else:
                st.error("PIN salah!")

if st.session_state["show_results"]:
    counts = get_vote_counts()
    total_votes = sum(counts.values())
    st.markdown(
        f"### Perolehan Suara Sementara (Total: **{total_votes}** Suara)"
    )

    rows = []
    for candidate in CANDIDATES:
        count = counts.get(str(candidate["id"]), 0)
        label = (
            candidate["name"]
            if candidate["id"] == "tidak-memilih"
            else f"No. {candidate['id']} - {candidate['name']}"
        )
        percentage = round((count / total_votes) * 100) if total_votes else 0
        rows.append(
            {"Kandidat": label, "Suara": count, "Persentase": percentage}
        )

    result_df = pd.DataFrame(rows).set_index("Kandidat")
    st.bar_chart(result_df["Suara"], color="#2563eb")
    st.dataframe(
        result_df,
        use_container_width=True,
        hide_index=False,
    )

if st.session_state.pop("reset_done", False):
    st.success("Data suara telah di-reset.")

st.markdown("</div>", unsafe_allow_html=True)
