"""
DeepSeek stub module for siakad-app.
Provides a simple, dependency-free DeepSeekModel for local development and testing.
"""
from typing import List, Any

class DeepSeekModel:
    """
    Simple stub for DeepSeek model.
    Methods:
    - load(): prepare model (noop)
    - encode(texts): returns list of simple vector representations (floats)
    - search(query, corpus, top_k=5): naive search returning top_k items by similarity
    """

    def __init__(self, name: str = "deepseek-stub"):
        self.name = name
        self._loaded = False

    def load(self) -> None:
        """Simulate loading resources."""
        self._loaded = True

    def encode(self, texts: List[str]) -> List[List[float]]:
        """Return simple bag-of-words counts as vector stub."""
        if not self._loaded:
            self.load()
        vectors = []
        for t in texts:
            tokens = t.split()
            token_counts = {}
            for tok in tokens:
                token_counts[tok] = token_counts.get(tok, 0) + 1
            vec = [float(sum(ord(c) for c in k) % 10) for k in list(token_counts)[:10]]
            vec = (vec + [0.0]*10)[:10]
            vectors.append(vec)
        return vectors

    def similarity(self, a: List[float], b: List[float]) -> float:
        dot = sum(x*y for x,y in zip(a,b))
        norm_a = sum(x*x for x in a) ** 0.5
        norm_b = sum(x*x for x in b) ** 0.5
        if norm_a == 0 or norm_b == 0:
            return 0.0
        return dot / (norm_a * norm_b)

    def search(self, query: str, corpus: List[str], top_k: int = 5) -> List[Any]:
        q_vec = self.encode([query])[0]
        corpus_vecs = self.encode(corpus)
        scores = [(i, self.similarity(q_vec, v)) for i,v in enumerate(corpus_vecs)]
        scores.sort(key=lambda x: x[1], reverse=True)
        return [{"index": i, "score": s, "text": corpus[i]} for i,s in scores[:top_k]]
