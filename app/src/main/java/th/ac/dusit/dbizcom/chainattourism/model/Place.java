package th.ac.dusit.dbizcom.chainattourism.model;

import com.google.gson.annotations.SerializedName;

public class Place {

    public enum PlaceType {
        TOUR,
        TEMPLE,
        RESTAURANT,
        OTOP
    }

    @SerializedName("id")
    public final int id;
    @SerializedName("name")
    public final String name;
    @SerializedName("district")
    public final String district;
    @SerializedName("address")
    public final String address;
    @SerializedName("details")
    public final String details;
    @SerializedName("phone")
    public final String phone;
    @SerializedName("opening_time")
    public final String openingTime;
    @SerializedName("latitude")
    public final double latitude;
    @SerializedName("longitude")
    public final double longitude;
    @SerializedName("image_list")
    public final String listImage;
    @SerializedName("image_cover")
    public final String coverImage;
    @SerializedName("recommend")
    public final boolean recommend;
    @SerializedName("place_type")
    public final PlaceType placeType;

    public Place(int id, String name, String district, String address, String details, String phone,
                 String openingTime, double latitude, double longitude, String listImage, String coverImage,
                 boolean recommend, PlaceType placeType) {
        this.id = id;
        this.name = name;
        this.district = district;
        this.address = address;
        this.details = details;
        this.phone = phone;
        this.openingTime = openingTime;
        this.latitude = latitude;
        this.longitude = longitude;
        this.listImage = listImage;
        this.coverImage = coverImage;
        this.recommend = recommend;
        this.placeType = placeType;
    }
}
