package th.ac.dusit.dbizcom.chainattourism.model;

import com.google.gson.annotations.SerializedName;

public class News {

    @SerializedName("id")
    public final int id;
    @SerializedName("title")
    public final String title;
    @SerializedName("image")
    public final String image;

    public News(int id, String title, String image) {
        this.id = id;
        this.title = title;
        this.image = image;
    }
}
